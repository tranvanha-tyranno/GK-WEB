<?php
declare(strict_types=1);
namespace App\Repositories;
use PDO;

final class MessageRepository
{
    private const SELECT = 'SELECT m.*, u.username, u.display_name FROM messages m JOIN users u ON u.id = m.user_id';
    public function __construct(private readonly PDO $db) {}
    public function latest(int $roomId, int $limit = 50): array
    {
        $query = $this->db->prepare(self::SELECT . ' WHERE m.room_id = ? ORDER BY m.id DESC LIMIT ?');
        $query->bindValue(1, $roomId, PDO::PARAM_INT);
        $query->bindValue(2, max(1, min(50, $limit)), PDO::PARAM_INT);
        $query->execute();
        return array_reverse(array_map(self::present(...), $query->fetchAll()));
    }
    public function before(int $roomId, int $beforeId, int $limit = 50): array
    {
        $query = $this->db->prepare(self::SELECT . ' WHERE m.room_id = ? AND m.id < ? ORDER BY m.id DESC LIMIT ?');
        $query->bindValue(1, $roomId, PDO::PARAM_INT);
        $query->bindValue(2, $beforeId, PDO::PARAM_INT);
        $query->bindValue(3, max(1, min(50, $limit)), PDO::PARAM_INT);
        $query->execute();
        return array_reverse(array_map(self::present(...), $query->fetchAll()));
    }
    public function search(int $roomId, string $term, int $limit = 50, ?int $beforeId = null): array
    {
        $needle = self::normalize($term);
        if ($needle === '') { return []; }
        $sql = self::SELECT . ' WHERE m.room_id = ?' . ($beforeId === null ? '' : ' AND m.id < ?') . ' ORDER BY m.id DESC';
        $query = $this->db->prepare($sql); $args = [$roomId]; if ($beforeId !== null) { $args[] = $beforeId; }
        $query->execute($args);
        $matches = [];
        while ($row = $query->fetch()) {
            $haystack = self::normalize($row['content'] . ' ' . $row['display_name'] . ' ' . $row['username']);
            if (str_contains($haystack, $needle)) {
                $matches[] = self::present($row);
                if (count($matches) >= max(1, min(51, $limit))) { break; }
            }
        }
        return array_reverse($matches);
    }
    public function after(int $roomId, int $afterId): array
    {
        $query = $this->db->prepare(self::SELECT . ' WHERE m.room_id = ? AND m.id > ? ORDER BY m.id LIMIT 1000');
        $query->execute([$roomId, $afterId]);
        return array_map(self::present(...), $query->fetchAll());
    }
    public function create(int $userId, int $roomId, string $content): array
    {
        $query = $this->db->prepare('INSERT INTO messages(user_id, room_id, content, created_at) VALUES (?, ?, ?, ?)');
        $query->execute([$userId, $roomId, $content, gmdate('Y-m-d\TH:i:s\Z')]);
        $read = $this->db->prepare(self::SELECT . ' WHERE m.id = ?');
        $read->execute([(int) $this->db->lastInsertId()]);
        return self::present($read->fetch());
    }
    private static function present(array $row): array
    {
        return [
            'id' => (int) $row['id'], 'roomId' => (int) $row['room_id'],
            'user' => ['id' => (int) $row['user_id'], 'username' => $row['username'], 'displayName' => $row['display_name']],
            'content' => $row['content'], 'createdAt' => $row['created_at'],
        ];
    }
    private static function normalize(string $text): string
    {
        $text = str_replace(['đ', 'Đ'], 'd', mb_strtolower($text, 'UTF-8'));
        $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        return strtolower($converted === false ? $text : $converted);
    }
}
