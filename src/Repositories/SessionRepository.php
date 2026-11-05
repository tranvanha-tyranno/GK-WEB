<?php
declare(strict_types=1);
namespace App\Repositories;
use PDO;

final class SessionRepository
{
    public function __construct(private readonly PDO $db) {}
    public function findByTokenHash(string $hash, ?int $now = null): ?array
    {
        $query = $this->db->prepare('SELECT s.*, u.username, u.display_name FROM sessions s LEFT JOIN users u ON u.id = s.user_id WHERE token_hash = ? AND revoked_at IS NULL AND expires_at > ?');
        $query->execute([$hash, $now ?? time()]);
        return $query->fetch() ?: null;
    }
    public function create(?int $userId, string $hash, string $csrf, int $expires): int
    {
        $query = $this->db->prepare('INSERT INTO sessions(user_id, token_hash, csrf_token, expires_at) VALUES (?, ?, ?, ?)');
        $query->execute([$userId, $hash, $csrf, $expires]);
        return (int) $this->db->lastInsertId();
    }
    public function isActive(int $id, ?int $now = null): bool
    {
        $query = $this->db->prepare('SELECT 1 FROM sessions WHERE id = ? AND revoked_at IS NULL AND expires_at > ?');
        $query->execute([$id, $now ?? time()]);
        return (bool) $query->fetchColumn();
    }
    public function revoke(int $id): void
    {
        $query = $this->db->prepare('UPDATE sessions SET revoked_at = ? WHERE id = ? AND revoked_at IS NULL');
        $query->execute([time(), $id]);
    }
}
