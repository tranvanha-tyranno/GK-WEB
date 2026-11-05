<?php
declare(strict_types=1);
namespace App\Realtime;

final class RateLimiter
{
    private array $hits = [];
    public function __construct(private readonly int $limit = 20, private readonly float $window = 60.0) {}
    public function canSend(int $userId, ?float $now = null): bool
    {
        $now ??= microtime(true);
        $this->hits[$userId] = array_values(array_filter($this->hits[$userId] ?? [], fn (float $hit): bool => $hit > $now - $this->window));
        return count($this->hits[$userId]) < $this->limit;
    }
    public function record(int $userId, ?float $now = null): void { $this->hits[$userId][] = $now ?? microtime(true); }
    public function cleanup(?float $now = null): void
    {
        foreach (array_keys($this->hits) as $userId) {
            $this->canSend($userId, $now);
            if (!$this->hits[$userId]) { unset($this->hits[$userId]); }
        }
    }
}
