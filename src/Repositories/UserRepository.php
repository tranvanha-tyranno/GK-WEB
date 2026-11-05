<?php
declare(strict_types=1);
namespace App\Repositories;
use PDO;

final class UserRepository
{
    public function __construct(private readonly PDO $db) {}
    public function findByUsername(string $username): ?array
    {
        $query = $this->db->prepare('SELECT * FROM users WHERE username = ?');
        $query->execute([$username]);
        return $query->fetch() ?: null;
    }
    public static function present(array $row): array
    {
        return ['id' => (int) $row['id'], 'username' => $row['username'], 'displayName' => $row['display_name']];
    }
}
