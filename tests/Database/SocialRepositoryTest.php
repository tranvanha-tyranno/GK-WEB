<?php
declare(strict_types=1);
namespace Tests\Database;
use App\Http\HttpError;
use App\Repositories\{SocialRepository, RoomRepository};
use App\Realtime\{Peer, RoomHub, EventHandler, RateLimiter};
use App\Repositories\MessageRepository;
use App\Services\ChatService;
use App\Shared\ProtocolError;
use Tests\TestCase;

final class SocialRepositoryTest extends TestCase
{
    private function social(): SocialRepository { return new SocialRepository($this->db); }
    private function connect(int $a, int $b): void { $this->social()->friendship($a, $b, 'send'); $this->social()->friendship($b, $a, 'accept'); }
    public function testOnlyRecipientCanAcceptAndFriendshipIsSymmetric(): void
    {
        $s = $this->social(); $s->friendship(1, 2, 'send');
        self::assertSame('outgoing', $s->people(1, 'requests')['people'][0]['relation']);
        self::assertSame('incoming', $s->people(2, 'requests')['people'][0]['relation']);
        try { $s->friendship(1, 2, 'accept'); self::fail('Sender must not accept'); } catch (HttpError) {}
        $s->friendship(2, 1, 'accept');
        self::assertCount(1, $s->people(1, 'friends')['people']); self::assertCount(1, $s->people(2, 'friends')['people']);
        self::assertSame([], $s->people(1, 'suggestions')['people']);
        $s->friendship(2, 1, 'remove'); self::assertSame([], $s->people(1, 'friends')['people']);
    }
    public function testGroupIsVisibleOnlyToSelectedMembersAndWebsocketRejectsOutsider(): void
    {
        $this->db->exec("INSERT INTO users(id, username, display_name, password_hash) VALUES(3, 'charlie', 'Charlie', 'unused')");
        $this->connect(1, 2);
        $room = $this->social()->createGroup(1, 'Private project', [2, 2]);
        $rooms = new RoomRepository($this->db);
        self::assertTrue($rooms->canAccess($room['id'], 1)); self::assertTrue($rooms->canAccess($room['id'], 2)); self::assertFalse($rooms->canAccess($room['id'], 3));
        self::assertCount(3, $rooms->all(2)); self::assertCount(2, $rooms->all(3));
        self::assertSame(2, $room['memberCount']);
        $hub = new RoomHub();
        $peer = new Peer(3, ['id' => 3, 'user' => ['id' => 3]], static function(string $raw): void {}, static function(): void {});
        $handler = new EventHandler($rooms, $hub, new ChatService(new MessageRepository($this->db), $hub, new RateLimiter()));
        $this->expectException(ProtocolError::class);
        $handler->handle($peer, json_encode(['type' => 'room:join', 'payload' => ['roomId' => $room['id']]]));
    }
    public function testInvalidGroupRollsBackWithoutCreatingPublicRoom(): void
    {
        try { $this->social()->createGroup(1, 'Unauthorized', [2]); self::fail('Non friend invited'); } catch (HttpError) {}
        self::assertSame(2, (int)$this->db->query('SELECT COUNT(*) FROM rooms')->fetchColumn());
        $this->social()->createGroup(1, 'Solo', []);
        try { $this->social()->createGroup(2, 'Solo', []); self::fail('Duplicate name'); } catch (HttpError) {}
        self::assertSame(1, (int)$this->db->query('SELECT COUNT(*) FROM private_groups')->fetchColumn());
    }
    public function testNearbyRequiresBothConsentsFiltersDistanceAndNeverReturnsCoordinates(): void
    {
        $s = $this->social();
        $s->location(2, ['latitude' => 10.776, 'longitude' => 106.7]);
        self::assertSame([], $s->people(1, 'nearby')['people']);
        $s->location(1, ['latitude' => 10.777, 'longitude' => 106.7]);
        $near = $s->people(1, 'nearby')['people']; self::assertCount(1, $near); self::assertLessThan(1, $near[0]['distanceKm']); self::assertArrayNotHasKey('latitude', $near[0]); self::assertArrayNotHasKey('longitude', $near[0]);
        $s->location(2, ['latitude' => 21.028, 'longitude' => 105.834]); self::assertSame([], $s->people(1, 'nearby')['people']);
        $s->location(2, ['latitude' => 10.776, 'longitude' => 106.7]);
        $this->db->exec('UPDATE user_locations SET updated_at = 1 WHERE user_id = 2'); self::assertSame([], $s->people(1, 'nearby')['people']);
        $s->location(1, ['enabled' => false]); self::assertFalse($s->people(1, 'nearby')['locationEnabled']);
    }
    public function testSuggestionsUseMutualFriendsAndSearchAndRandomExcludeRelations(): void
    {
        $this->db->exec("INSERT INTO users(id, username, display_name, password_hash) VALUES (3, 'cuong', 'Cường Đặng', 'unused'), (4, 'david', 'David', 'unused')");
        $this->connect(1, 2); $this->connect(2, 3);
        $s = $this->social(); $suggestions = $s->people(1, 'suggestions')['people'];
        self::assertSame(3, $suggestions[0]['id']); self::assertSame(1, $suggestions[0]['mutualFriends']);
        self::assertCount(1, $s->people(1, 'all', 'cuong dang')['people']);
        $s->friendship(1, 4, 'send'); self::assertSame([3], array_column($s->people(1, 'random')['people'], 'id'));
        self::assertSame([], $s->people(1, 'all', 'nobody')['people']);
    }
    public function testSharedGroupRemainsARecommendationAfterUnfriending(): void
    {
        $this->connect(1, 2); $s = $this->social();
        $s->createGroup(1, 'Shared project', [2]); $s->friendship(1, 2, 'remove');
        $people = $s->people(1, 'suggestions')['people'];
        self::assertSame(1, $people[0]['sharedGroups']);
        self::assertSame('none', $people[0]['relation']);
    }
    public function testInvalidLocationAndSelfFriendshipAreRejected(): void
    {
        foreach ([['latitude' => 91, 'longitude' => 0], ['latitude' => 0, 'longitude' => -181], ['latitude' => '10', 'longitude' => 0]] as $input) {
            try { $this->social()->location(1, $input); self::fail('Invalid location accepted'); } catch (HttpError) {}
        }
        self::assertSame(0, (int)$this->db->query('SELECT COUNT(*) FROM user_locations')->fetchColumn());
        $this->expectException(HttpError::class); $this->social()->friendship(1, 1, 'send');
    }
}
