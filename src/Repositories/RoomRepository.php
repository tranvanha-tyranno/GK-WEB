<?php
declare(strict_types=1);
namespace App\Repositories;
use PDO;

final class RoomRepository
{
    public function __construct(private readonly PDO $db) {}
    public function all(?int $userId = null): array
    {
        $query = $this->db->prepare("SELECT r.id, CASE WHEN d.room_id IS NOT NULL THEN 'Trò chuyện với ' || CASE WHEN d.user_low = ? THEN high.display_name ELSE low.display_name END ELSE r.name END AS room_name, g.owner_id, d.room_id AS direct_room, (SELECT COUNT(*) FROM group_members m WHERE m.room_id = r.id) AS member_count FROM rooms r LEFT JOIN private_groups g ON g.room_id = r.id LEFT JOIN direct_chats d ON d.room_id = r.id LEFT JOIN users low ON low.id = d.user_low LEFT JOIN users high ON high.id = d.user_high WHERE (g.room_id IS NULL OR EXISTS(SELECT 1 FROM group_members m WHERE m.room_id = r.id AND m.user_id = ?)) AND (d.room_id IS NULL OR EXISTS(SELECT 1 FROM group_members m WHERE m.room_id = r.id AND m.user_id = ?)) ORDER BY r.id");
        $query->execute([$userId, $userId, $userId]);
        return array_map(static fn(array $r): array => ['id' => (int)$r['id'], 'name' => $r['room_name'], 'private' => $r['owner_id'] !== null, 'direct' => $r['direct_room'] !== null, 'isOwner' => $r['owner_id'] !== null && $r['direct_room'] === null && (int)$r['owner_id'] === (int)$userId, 'memberCount' => (int)$r['member_count']], $query->fetchAll());
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
