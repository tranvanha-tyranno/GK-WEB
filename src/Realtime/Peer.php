<?php
declare(strict_types=1);
namespace App\Realtime;
use App\Shared\Protocol;
use Closure;

final class Peer
{
    public ?int $roomId = null;
    public float $typingUntil = 0;
    public float $lastPong;
    public float $lastPing;
    public function __construct(public readonly int $id, public readonly array $session, private readonly Closure $sender, private readonly Closure $closer)
    { $this->lastPong = $this->lastPing = microtime(true); }
    public function user(): array { return $this->session['user']; }
    public function send(string $type, array $payload): void { ($this->sender)(Protocol::encode($type, $payload)); }
    public function close(): void { ($this->closer)(); }
}
