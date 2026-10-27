<?php
declare(strict_types=1);
namespace App\Repositories;
use PDO;

final class RoomRepository
{
    public function __construct(private readonly PDO $db) {}
    public function all(): array
    {
        return array_map(static fn (array $row): array => ['id' => (int) $row['id'], 'name' => $row['name']], $this->db->query('SELECT id, name FROM rooms ORDER BY id')->fetchAll());
    }
    public function exists(int $id): bool
    {
        $query = $this->db->prepare('SELECT 1 FROM rooms WHERE id = ?');
        $query->execute([$id]);
        return (bool) $query->fetchColumn();
    }
}
