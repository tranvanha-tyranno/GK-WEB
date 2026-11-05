<?php
declare(strict_types=1);
namespace App\Repositories;
use PDO;

final class RoomRepository
{
    public function __construct(private readonly PDO $db) {}
    public function all(?int $userId = null): array
    {
        $query = $this->db->prepare('SELECT r.id, r.name, g.owner_id, (SELECT COUNT(*) FROM group_members m WHERE m.room_id = r.id) AS member_count FROM rooms r LEFT JOIN private_groups g ON g.room_id = r.id WHERE g.room_id IS NULL OR EXISTS(SELECT 1 FROM group_members m WHERE m.room_id = r.id AND m.user_id = ?) ORDER BY r.id');
        $query->execute([$userId]);
        return array_map(static fn(array $r): array => ['id' => (int)$r['id'], 'name' => $r['name'], 'private' => $r['owner_id'] !== null, 'memberCount' => (int)$r['member_count']], $query->fetchAll());
    }
    public function exists(int $id): bool
    {
        $query = $this->db->prepare('SELECT 1 FROM rooms WHERE id = ?');
        $query->execute([$id]);
        return (bool)$query->fetchColumn();
    }
    public function canAccess(int $id, int $userId): bool
    {
        $query = $this->db->prepare('SELECT 1 FROM rooms r WHERE r.id = ? AND (NOT EXISTS(SELECT 1 FROM private_groups g WHERE g.room_id = r.id) OR EXISTS(SELECT 1 FROM group_members m WHERE m.room_id = r.id AND m.user_id = ?))');
        $query->execute([$id, $userId]);
        return (bool)$query->fetchColumn();
    }
}
