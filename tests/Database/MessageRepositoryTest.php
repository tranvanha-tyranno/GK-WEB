<?php
declare(strict_types=1);
namespace Tests\Database;
use App\Repositories\MessageRepository;
use Tests\TestCase;

final class MessageRepositoryTest extends TestCase
{
    public function testLatestReturnsNewestFiftyInAscendingOrderAndDoesNotLeakRooms(): void
    {
        $repo = new MessageRepository($this->db);
        for ($i = 1; $i <= 60; $i++) { $repo->create(1, 1, "message $i"); }
        $repo->create(2, 2, 'private to room two');
        $messages = $repo->latest(1);
        self::assertCount(50, $messages); self::assertSame('message 11', $messages[0]['content']); self::assertSame('message 60', $messages[49]['content']);
        self::assertSame(1, $messages[0]['user']['id']); self::assertSame([1], array_values(array_unique(array_column($messages, 'roomId'))));
    }
    public function testBeforeLoadsOlderPagesInAscendingOrderWithoutRepeatingCursor(): void
    {
        $repo = new MessageRepository($this->db); $ids = [];
        for ($i = 1; $i <= 125; $i++) { $ids[] = $repo->create(1, 1, "message $i")['id']; }
        $page = $repo->before(1, $ids[75], 50);
        self::assertCount(50, $page);
        self::assertSame('message 26', $page[0]['content']);
        self::assertSame('message 75', $page[49]['content']);
        self::assertSame([], $repo->before(2, $ids[75], 50));
        self::assertSame([], $repo->before(1, $ids[0], 50));
    }
    public function testSearchFindsOldMessagesAndIgnoresVietnameseAccents(): void
    {
        $repo = new MessageRepository($this->db);
        for ($i = 1; $i <= 80; $i++) { $repo->create(1, 1, $i === 2 ? 'Trao đổi kỹ thuật' : "message $i"); }
        $matches = $repo->search(1, 'ky thuat', 10);
        self::assertCount(1, $matches);
        self::assertSame('Trao đổi kỹ thuật', $matches[0]['content']);
        self::assertSame([], $repo->search(2, 'ky thuat'));
    }
    public function testSearchCursorReturnsOlderMatchesWithoutRepeating(): void
    {
        $repo = new MessageRepository($this->db); $ids = [];
        for ($i = 1; $i <= 60; $i++) { $ids[] = $repo->create(1, 1, 'needle ' . $i)['id']; }
        $newest = $repo->search(1, 'needle', 11);
        self::assertCount(11, $newest);
        $older = $repo->search(1, 'needle', 11, $newest[0]['id']);
        self::assertSame('needle 39', $older[0]['content']);
        self::assertSame('needle 49', $older[10]['content']);
        self::assertLessThan($newest[0]['id'], $older[10]['id']);
    }
    public function testSqlLikeTextIsStoredAsDataAndCursorWorks(): void
    {
        $repo = new MessageRepository($this->db); $first = $repo->create(1, 1, 'first');
        $second = $repo->create(1, 1, "'); DROP TABLE users; --");
        self::assertSame([$second], $repo->after(1, $first['id']));
        self::assertSame(2, (int) $this->db->query('SELECT COUNT(*) FROM users')->fetchColumn());
    }
    public function testForeignKeysRejectUnknownUser(): void
    {
        $this->expectException(\PDOException::class); (new MessageRepository($this->db))->create(999, 1, 'invalid');
    }
}
