<?php
declare(strict_types=1);
namespace Tests\Realtime;
use App\Realtime\{Peer, RoomHub, RateLimiter, EventHandler};
use App\Repositories\{MessageRepository, RoomRepository};
use App\Services\ChatService;
use App\Shared\ProtocolError;
use Tests\TestCase;

final class ChatTest extends TestCase
{
    private function peer(int $id, int $userId, array &$events): Peer
    {
        return new Peer($id, ['id' => $id, 'user' => ['id' => $userId, 'username' => "user$userId", 'displayName' => "User $userId"]], static function (string $raw) use (&$events): void { $events[] = json_decode($raw, true); }, static function (): void {});
    }
    public function testPresenceDeduplicatesTabsAndBroadcastIsIsolated(): void
    {
        $hub = new RoomHub(); $aEvents = $bEvents = $otherEvents = [];
        $a = $this->peer(1, 1, $aEvents); $b = $this->peer(2, 1, $bEvents); $other = $this->peer(3, 2, $otherEvents);
        foreach ([$a, $b, $other] as $peer) { $hub->attach($peer); }
        $hub->join($a, 1); $hub->join($b, 1); $hub->join($other, 2);
        $presence = array_values(array_filter($aEvents, static fn ($e) => $e['type'] === 'room:presence'));
        self::assertCount(1, end($presence)['payload']['users']);
        $aEvents = $bEvents = $otherEvents = [];
        $hub->broadcast(1, 'chat:new', ['id' => 10]);
        self::assertCount(1, $aEvents); self::assertCount(1, $bEvents); self::assertSame([], $otherEvents);
        $hub->detach($a); self::assertSame(1, $b->roomId);
    }
    public function testSwitchRoomCleansTypingAndMembershipAndExpiryWorks(): void
    {
        $hub = new RoomHub(); $events = []; $peer = $this->peer(1, 1, $events); $hub->attach($peer); $hub->join($peer, 1);
        $hub->typing($peer, true); self::assertGreaterThan(0, $peer->typingUntil);
        $hub->join($peer, 2); self::assertSame(0.0, $peer->typingUntil);
        $hub->typing($peer, true); $hub->expireTyping(microtime(true) + 4); self::assertSame(0.0, $peer->typingUntil);
        $hub->detach($peer); self::assertSame([], $hub->all());
    }
    public function testSlidingWindowIsPerUserAndBoundaryExpires(): void
    {
        $limiter = new RateLimiter();
        for ($i = 0; $i < 20; $i++) { self::assertTrue($limiter->canSend(1, (float) $i)); $limiter->record(1, (float) $i); }
        self::assertFalse($limiter->canSend(1, 20)); self::assertTrue($limiter->canSend(2, 20));
        self::assertTrue($limiter->canSend(1, 60)); $limiter->record(1, 60);
        self::assertFalse($limiter->canSend(1, 60));
    }
    public function testSavesBeforeBroadcastAndCannotSpoofRoom(): void
    {
        $hub = new RoomHub(); $events = []; $peer = $this->peer(1, 1, $events); $hub->attach($peer); $hub->join($peer, 1);
        $service = new ChatService(new MessageRepository($this->db), $hub, new RateLimiter());
        $message = $service->send($peer, 1, 'hello');
        self::assertSame(1, (int) $this->db->query('SELECT COUNT(*) FROM messages')->fetchColumn());
        self::assertSame('chat:new', end($events)['type']); self::assertSame($message, end($events)['payload']);
        $this->expectException(ProtocolError::class); $service->send($peer, 2, 'wrong room');
    }
    public function testPersistenceFailureDoesNotBroadcastOrConsumeRateLimit(): void
    {
        $hub = new RoomHub(); $events = []; $peer = $this->peer(1, 1, $events); $hub->attach($peer); $hub->join($peer, 1); $events = [];
        $limiter = new RateLimiter(1);
        $this->db->exec("CREATE TRIGGER fail_insert BEFORE INSERT ON messages BEGIN SELECT RAISE(ABORT, 'simulated write failure'); END");
        $service = new ChatService(new MessageRepository($this->db), $hub, $limiter);
        try { $service->send($peer, 1, 'must fail'); self::fail('Expected a persistence error'); } catch (\PDOException) {}
        self::assertSame([], $events); self::assertTrue($limiter->canSend(1));
        $this->db->exec('DROP TRIGGER fail_insert');
        self::assertSame('now works', $service->send($peer, 1, 'now works')['content']);
    }
    public function testEventHandlerRejectsNonexistentRoom(): void
    {
        $hub = new RoomHub(); $events = []; $peer = $this->peer(1, 1, $events);
        $handler = new EventHandler(new RoomRepository($this->db), $hub, new ChatService(new MessageRepository($this->db), $hub, new RateLimiter()));
        $this->expectException(ProtocolError::class); $handler->handle($peer, '{"type":"room:join","payload":{"roomId":999}}');
    }
}
