<?php
declare(strict_types=1);
namespace App\Realtime;

final class RoomHub
{
    /** @var array<int, Peer> */
    private array $peers = [];
    public function attach(Peer $peer): void { $this->peers[$peer->id] = $peer; }
    public function all(): array { return array_values($this->peers); }
    public function detach(Peer $peer): void
    {
        $old = $peer->roomId;
        unset($this->peers[$peer->id]);
        $peer->roomId = null;
        $peer->typingUntil = 0;
        if ($old !== null) { $this->states($old); }
    }
    public function join(Peer $peer, int $roomId): void
    {
        $old = $peer->roomId;
        $peer->roomId = $roomId;
        $peer->typingUntil = 0;
        $peer->send('room:joined', ['roomId' => $roomId]);
        if ($old !== null && $old !== $roomId) { $this->states($old); }
        $this->states($roomId);
    }
    public function broadcast(int $roomId, string $type, array $payload): void
    {
        foreach ($this->all() as $peer) { if ($peer->roomId === $roomId) { $peer->send($type, $payload); } }
    }
    public function typing(Peer $peer, bool $typing, ?float $now = null): void
    {
        $peer->typingUntil = $typing ? ($now ?? microtime(true)) + 3 : 0;
        if ($peer->roomId !== null) { $this->states($peer->roomId); }
    }
    public function expireTyping(?float $now = null): void
    {
        $now ??= microtime(true);
        $changed = [];
        foreach ($this->all() as $peer) {
            if ($peer->typingUntil > 0 && $peer->typingUntil <= $now) {
                $peer->typingUntil = 0;
                if ($peer->roomId !== null) { $changed[$peer->roomId] = true; }
            }
        }
        foreach (array_keys($changed) as $roomId) { $this->states($roomId); }
    }
    public function states(int $roomId): void
    {
        $users = $typing = [];
        foreach ($this->all() as $peer) {
            if ($peer->roomId !== $roomId) { continue; }
            $users[$peer->user()['id']] = $peer->user();
            if ($peer->typingUntil > microtime(true)) { $typing[$peer->user()['id']] = $peer->user(); }
        }
        $this->broadcast($roomId, 'room:presence', ['roomId' => $roomId, 'users' => array_values($users)]);
        $this->broadcast($roomId, 'room:typing', ['roomId' => $roomId, 'users' => array_values($typing)]);
    }
}
