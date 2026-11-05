<?php
declare(strict_types=1);
namespace App\Services;
use App\Realtime\{Peer, RateLimiter, RoomHub};
use App\Repositories\MessageRepository;
use App\Shared\{ProtocolError, Validator};

final class ChatService
{
    public function __construct(private readonly MessageRepository $messages, private readonly RoomHub $hub, private readonly RateLimiter $limiter) {}
    public function send(Peer $peer, int $roomId, string $content): array
    {
        if ($peer->roomId !== $roomId) { throw new ProtocolError('NOT_IN_ROOM', 'Hãy tham gia phòng này trước khi gửi tin.'); }
        $content = Validator::content($content);
        $userId = $peer->user()['id'];
        if (!$this->limiter->canSend($userId)) { throw new ProtocolError('RATE_LIMITED', 'Bạn được gửi tối đa 20 tin nhắn mỗi phút. Vui lòng đợi.'); }
        // SQLite autocommits the insert before any client receives a notification.
        $message = $this->messages->create($userId, $roomId, $content);
        $this->limiter->record($userId);
        $this->hub->typing($peer, false);
        $this->hub->broadcast($roomId, 'chat:new', $message);
        return $message;
    }
}
