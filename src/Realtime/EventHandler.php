<?php
declare(strict_types=1);
namespace App\Realtime;
use App\Repositories\RoomRepository;
use App\Services\ChatService;
use App\Shared\{ProtocolError, Validator};

final class EventHandler
{
    public function __construct(private readonly RoomRepository $rooms, private readonly RoomHub $hub, private readonly ChatService $chat) {}
    public function handle(Peer $peer, string $raw): void
    {
        $event = Validator::event($raw);
        $payload = $event['payload'];
        if (!$this->rooms->canAccess($payload['roomId'], $peer->user()['id'])) { throw new ProtocolError('ROOM_NOT_FOUND', 'Phòng không tồn tại hoặc bạn chưa được thêm vào nhóm.'); }
        if ($event['type'] === 'room:join') {
            if (!$this->rooms->exists($payload['roomId'])) { throw new ProtocolError('ROOM_NOT_FOUND', 'Phòng trò chuyện này không tồn tại.'); }
            $this->hub->join($peer, $payload['roomId']);
            return;
        }
        if ($peer->roomId !== $payload['roomId']) { throw new ProtocolError('NOT_IN_ROOM', 'Hãy tham gia phòng này trước.'); }
        if ($event['type'] === 'typing:set') { $this->hub->typing($peer, $payload['isTyping']); return; }
        $this->chat->send($peer, $payload['roomId'], $payload['content']);
    }
}
