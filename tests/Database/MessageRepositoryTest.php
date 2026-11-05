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
