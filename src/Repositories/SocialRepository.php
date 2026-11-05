<?php
declare(strict_types=1);
namespace App\Repositories;
use App\Http\HttpError;
use PDO;

final class SocialRepository
{
    public function __construct(private readonly PDO $db) {}
    private function rows(string $sql, array $args = []): array
    {
        $q = $this->db->prepare($sql);
        foreach ($args as $i => $value) { $q->bindValue($i + 1, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR); }
        $q->execute(); return $q->fetchAll();
    }
    public function people(int $self, string $mode, string $search = ''): array
    {
        $rows = $this->rows('SELECT u.id, u.username, u.display_name, f.status, f.sender_id,
            (SELECT COUNT(*) FROM group_members a JOIN group_members b ON a.room_id = b.room_id WHERE a.user_id = ? AND b.user_id = u.id) AS shared_groups,
            (SELECT COUNT(*) FROM friendships a JOIN friendships b ON (CASE WHEN a.user_low = ? THEN a.user_high ELSE a.user_low END) = (CASE WHEN b.user_low = u.id THEN b.user_high ELSE b.user_low END) WHERE a.status = \'accepted\' AND b.status = \'accepted\' AND ? IN (a.user_low, a.user_high) AND u.id IN (b.user_low, b.user_high)) AS mutual_friends,
            l.latitude, l.longitude FROM users u LEFT JOIN friendships f ON f.user_low = min(?, u.id) AND f.user_high = max(?, u.id)
            LEFT JOIN user_locations l ON l.user_id = u.id AND l.updated_at > ? WHERE u.id != ?', [$self, $self, $self, $self, $self, time() - 86400, $self]);
        $location = $this->rows('SELECT latitude, longitude FROM user_locations WHERE user_id = ? AND updated_at > ?', [$self, time() - 86400])[0] ?? null;
        $result = [];
        foreach ($rows as $r) {
            $relation = $r['status'] === 'accepted' ? 'friends' : ($r['status'] === 'pending' ? ((int)$r['sender_id'] === $self ? 'outgoing' : 'incoming') : 'none');
            $distance = null;
            if ($location && $r['latitude'] !== null) {
                $a = sin(deg2rad((float)$r['latitude'] - (float)$location['latitude']) / 2) ** 2 + cos(deg2rad((float)$location['latitude'])) * cos(deg2rad((float)$r['latitude'])) * sin(deg2rad((float)$r['longitude'] - (float)$location['longitude']) / 2) ** 2;
                $distance = 6371 * 2 * asin(sqrt(min(1, max(0, $a))));
            }
            if ($search !== '' && !str_contains(self::normalize($r['display_name'] . ' ' . $r['username']), self::normalize($search))) { continue; }
            if ($mode === 'friends' && $relation !== 'friends') { continue; }
            if ($mode === 'requests' && !in_array($relation, ['incoming', 'outgoing'], true)) { continue; }
            if (in_array($mode, ['suggestions', 'random'], true) && $relation !== 'none') { continue; }
            if ($mode === 'nearby' && ($distance === null || $distance > 10)) { continue; }
            $result[] = ['id' => (int)$r['id'], 'username' => $r['username'], 'displayName' => $r['display_name'], 'relation' => $relation, 'sharedGroups' => (int)$r['shared_groups'], 'mutualFriends' => (int)$r['mutual_friends'], 'distanceKm' => $distance === null ? null : round($distance, 1)];
        }
        if ($mode === 'random') { shuffle($result); }
        elseif ($mode === 'nearby') { usort($result, static fn($a, $b) => $a['distanceKm'] <=> $b['distanceKm']); }
        else { usort($result, static fn($a, $b) => ($b['mutualFriends'] <=> $a['mutualFriends']) ?: ($b['sharedGroups'] <=> $a['sharedGroups']) ?: strcasecmp($a['displayName'], $b['displayName'])); }
        return ['people' => array_slice($result, 0, 100), 'locationEnabled' => $location !== null];
    }
    private static function normalize(string $text): string
    {
        $text = str_replace(['đ', 'Đ'], 'd', mb_strtolower($text));
        $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        return strtolower($converted === false ? $text : $converted);
    }
    public function friendship(int $self, int $target, string $action): void
    {
        if ($self === $target || !$this->rows('SELECT id FROM users WHERE id = ?', [$target])) { throw new HttpError(400, 'INVALID_USER', 'Người dùng không hợp lệ.'); }
        $pair = [min($self, $target), max($self, $target)];
        if ($action === 'send') {
            $q = $this->db->prepare("INSERT OR IGNORE INTO friendships(user_low, user_high, sender_id, status) VALUES (?, ?, ?, 'pending')"); $q->execute([...$pair, $self]);
            if (!$q->rowCount()) { throw new HttpError(409, 'RELATION_EXISTS', 'Quan hệ đã thay đổi. Hãy cập nhật danh sách.'); }
        } elseif ($action === 'accept') {
            $q = $this->db->prepare("UPDATE friendships SET status = 'accepted' WHERE user_low = ? AND user_high = ? AND sender_id = ? AND status = 'pending'"); $q->execute([...$pair, $target]);
            if (!$q->rowCount()) { throw new HttpError(409, 'NO_REQUEST', 'Không còn lời mời để chấp nhận.'); }
        } elseif ($action === 'remove') {
            $q = $this->db->prepare('DELETE FROM friendships WHERE user_low = ? AND user_high = ?'); $q->execute($pair);
        } else { throw new HttpError(400, 'INVALID_ACTION', 'Thao tác không hợp lệ.'); }
    }
    public function location(int $self, array $input): void
    {
        if (($input['enabled'] ?? null) === false) {
            $q = $this->db->prepare('DELETE FROM user_locations WHERE user_id = ?'); $q->execute([$self]); return;
        }
        $lat = $input['latitude'] ?? null; $lon = $input['longitude'] ?? null;
        if ((!is_int($lat) && !is_float($lat)) || (!is_int($lon) && !is_float($lon)) || !is_finite((float)$lat) || !is_finite((float)$lon) || abs($lat) > 90 || abs($lon) > 180) { throw new HttpError(400, 'INVALID_LOCATION', 'Vị trí không hợp lệ.'); }
        $q = $this->db->prepare('INSERT INTO user_locations(user_id, latitude, longitude, updated_at) VALUES (?, ?, ?, ?) ON CONFLICT(user_id) DO UPDATE SET latitude = excluded.latitude, longitude = excluded.longitude, updated_at = excluded.updated_at'); $q->execute([$self, $lat, $lon, time()]);
    }
    public function createGroup(int $self, string $name, array $members): array
    {
        $name = trim($name);
        if (mb_strlen($name) < 2 || mb_strlen($name) > 60 || preg_match('/[\x00-\x1f\x7f]/u', $name)) { throw new HttpError(400, 'INVALID_NAME', 'Tên nhóm cần từ 2 đến 60 ký tự, không chứa ký tự điều khiển.'); }
        if (count($members) > 30 || array_filter($members, static fn($id) => !is_int($id) || $id < 1)) { throw new HttpError(400, 'INVALID_MEMBERS', 'Chọn tối đa 30 người bạn hợp lệ.'); }
        $members = array_values(array_unique(array_filter($members, static fn($id) => $id !== $self)));
        $this->db->beginTransaction();
        try {
            foreach ($members as $id) {
                if (!$this->rows("SELECT 1 FROM friendships WHERE user_low = ? AND user_high = ? AND status = 'accepted'", [min($self, $id), max($self, $id)])) { throw new HttpError(400, 'NOT_FRIENDS', 'Chỉ có thể thêm người đã kết bạn vào nhóm.'); }
            }
            $q = $this->db->prepare('INSERT OR IGNORE INTO rooms(name) VALUES (?)'); $q->execute([$name]);
            if (!$q->rowCount()) { throw new HttpError(409, 'NAME_EXISTS', 'Tên nhóm đã được sử dụng. Hãy chọn tên khác.'); }
            $id = (int)$this->db->lastInsertId();
            $q = $this->db->prepare('INSERT INTO private_groups(room_id, owner_id) VALUES (?, ?)'); $q->execute([$id, $self]);
            $q = $this->db->prepare('INSERT INTO group_members(room_id, user_id) VALUES (?, ?)');
            foreach ([$self, ...$members] as $member) { $q->execute([$id, $member]); }
            $this->db->commit();
            return ['id' => $id, 'name' => $name, 'private' => true, 'memberCount' => count($members) + 1];
        } catch (\Throwable $e) { $this->db->rollBack(); throw $e; }
    }
}
