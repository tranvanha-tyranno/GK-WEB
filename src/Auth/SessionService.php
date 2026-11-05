<?php
declare(strict_types=1);
namespace App\Auth;
use App\Repositories\SessionRepository;
use RuntimeException;

final class SessionService
{
    public function __construct(private readonly SessionRepository $repository, private readonly string $secret)
    {
        if (strlen($secret) < 32) { throw new RuntimeException('SESSION_SECRET must have at least 32 characters.'); }
    }
    /** Token is returned only to the HTTP cookie setter, never serialized as API data. */
    public function issue(?int $userId = null, ?int $oldSessionId = null): array
    {
        $token = bin2hex(random_bytes(32));
        $csrf = bin2hex(random_bytes(32));
        $expires = time() + 8 * 3600;
        $id = $this->repository->create($userId, hash('sha256', $token), $csrf, $expires);
        if ($oldSessionId !== null) { $this->revoke($oldSessionId); }
        return ['id' => $id, 'cookie' => $token . '.' . hash_hmac('sha256', $token, $this->secret), 'csrfToken' => $csrf, 'expires' => $expires];
    }
    public function resolveCookie(?string $cookie): ?array
    {
        if ($cookie === null || !preg_match('/\A([a-f0-9]{64})\.([a-f0-9]{64})\z/D', $cookie, $parts)) { return null; }
        if (!hash_equals(hash_hmac('sha256', $parts[1], $this->secret), $parts[2])) { return null; }
        $row = $this->repository->findByTokenHash(hash('sha256', $parts[1]));
        if ($row === null) { return null; }
        return [
            'id' => (int) $row['id'], 'csrfToken' => $row['csrf_token'], 'expires' => (int) $row['expires_at'],
            'user' => $row['user_id'] === null ? null : ['id' => (int) $row['user_id'], 'username' => $row['username'], 'displayName' => $row['display_name']],
        ];
    }
    public function isActive(int $sessionId): bool { return $this->repository->isActive($sessionId); }
    public function revoke(int $sessionId): void { $this->repository->revoke($sessionId); }
}
