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
        if (!in_array($mode, ['all', 'friends', 'requests', 'suggestions'], true)) { throw new HttpError(400, 'INVALID_QUERY', 'Bộ lọc không hợp lệ.'); }
        $rows = $this->rows('SELECT u.id, u.username, u.display_name, f.status, f.sender_id,
            (SELECT COUNT(*) FROM group_members a JOIN group_members b ON a.room_id = b.room_id WHERE a.user_id = ? AND b.user_id = u.id) AS shared_groups,
            (SELECT COUNT(*) FROM friendships a JOIN friendships b ON (CASE WHEN a.user_low = ? THEN a.user_high ELSE a.user_low END) = (CASE WHEN b.user_low = u.id THEN b.user_high ELSE b.user_low END) WHERE a.status = \'accepted\' AND b.status = \'accepted\' AND ? IN (a.user_low, a.user_high) AND u.id IN (b.user_low, b.user_high)) AS mutual_friends
            FROM users u LEFT JOIN friendships f ON f.user_low = min(?, u.id) AND f.user_high = max(?, u.id) WHERE u.id != ?', [$self, $self, $self, $self, $self, $self]);
        $result = [];
        foreach ($rows as $r) {
            $relation = $r['status'] === 'accepted' ? 'friends' : ($r['status'] === 'pending' ? ((int)$r['sender_id'] === $self ? 'outgoing' : 'incoming') : 'none');
            if ($search !== '' && !str_contains(self::normalize($r['display_name'] . ' ' . $r['username']), self::normalize($search))) { continue; }
            if ($mode === 'friends' && $relation !== 'friends') { continue; }
            if ($mode === 'requests' && !in_array($relation, ['incoming', 'outgoing'], true)) { continue; }
            if ($mode === 'suggestions' && $relation !== 'none') { continue; }
            $result[] = ['id' => (int)$r['id'], 'username' => $r['username'], 'displayName' => $r['display_name'], 'relation' => $relation, 'sharedGroups' => (int)$r['shared_groups'], 'mutualFriends' => (int)$r['mutual_friends']];
        }
        usort($result, static fn($a, $b) => (in_array($b['relation'], ['incoming', 'outgoing'], true) <=> in_array($a['relation'], ['incoming', 'outgoing'], true)) ?: ($b['mutualFriends'] <=> $a['mutualFriends']) ?: ($b['sharedGroups'] <=> $a['sharedGroups']) ?: strcasecmp($a['displayName'], $b['displayName']));
        return ['people' => array_slice($result, 0, 100)];
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
    public function openDirectMessage(int $self, int $target): array
    {
        if ($self === $target || !$this->rows("SELECT 1 FROM friendships WHERE user_low = ? AND user_high = ? AND status = 'accepted'", [min($self, $target), max($self, $target)])) { throw new HttpError(403, 'NOT_FRIENDS', 'Chỉ có thể nhắn riêng với người bạn đã kết nối.'); }
        $low = min($self, $target); $high = max($self, $target);
        $existing = $this->rows('SELECT room_id FROM direct_chats WHERE user_low = ? AND user_high = ?', [$low, $high]);
        if ($existing) { return ['id' => (int)$existing[0]['room_id']]; }
        $this->db->beginTransaction();
        try {
            $name = '__dm_' . $low . '_' . $high . '_' . bin2hex(random_bytes(4));
            $q = $this->db->prepare('INSERT INTO rooms(name) VALUES (?)'); $q->execute([$name]); $roomId = (int)$this->db->lastInsertId();
            $q = $this->db->prepare('INSERT INTO private_groups(room_id, owner_id) VALUES (?, ?)'); $q->execute([$roomId, $low]);
            $q = $this->db->prepare('INSERT INTO group_members(room_id, user_id) VALUES (?, ?)'); $q->execute([$roomId, $low]); $q->execute([$roomId, $high]);
            $q = $this->db->prepare('INSERT INTO direct_chats(room_id, user_low, user_high) VALUES (?, ?, ?)'); $q->execute([$roomId, $low, $high]);
            $this->db->commit(); return ['id' => $roomId];
        } catch (\Throwable $error) { $this->db->rollBack(); throw $error; }
    }
    public function groupDetails(int $self, int $roomId): array
    {
        $group = $this->rows('SELECT r.name, g.owner_id, EXISTS(SELECT 1 FROM direct_chats d WHERE d.room_id = r.id) AS direct FROM rooms r JOIN private_groups g ON g.room_id = r.id WHERE r.id = ?', [$roomId])[0] ?? null;
        if ($group === null || (int)$group['direct'] === 1 || !$this->rows('SELECT 1 FROM group_members WHERE room_id = ? AND user_id = ?', [$roomId, $self])) { throw new HttpError(404, 'ROOM_NOT_FOUND', 'Không tìm thấy nhóm hoặc bạn không còn là thành viên.'); }
        $members = $this->rows('SELECT u.id, u.username, u.display_name FROM group_members m JOIN users u ON u.id = m.user_id WHERE m.room_id = ? ORDER BY u.display_name', [$roomId]);
        $friends = $this->rows("SELECT u.id, u.username, u.display_name FROM users u JOIN friendships f ON f.status = 'accepted' AND f.user_low = min(?, u.id) AND f.user_high = max(?, u.id) WHERE u.id <> ? AND NOT EXISTS(SELECT 1 FROM group_members m WHERE m.room_id = ? AND m.user_id = u.id) ORDER BY u.display_name", [$self, $self, $self, $roomId]);
        return ['id' => $roomId, 'name' => $group['name'], 'ownerId' => (int)$group['owner_id'], 'isOwner' => (int)$group['owner_id'] === $self, 'members' => array_map(static fn(array $m): array => ['id' => (int)$m['id'], 'username' => $m['username'], 'displayName' => $m['display_name']], $members), 'friends' => array_map(static fn(array $m): array => ['id' => (int)$m['id'], 'username' => $m['username'], 'displayName' => $m['display_name']], $friends)];
    }
    public function manageGroup(int $self, int $roomId, string $action, array $input = []): array
    {
        $details = $this->groupDetails($self, $roomId);
        $owner = $details['isOwner'];
        if (in_array($action, ['rename', 'add', 'remove', 'delete'], true) && !$owner) { throw new HttpError(403, 'OWNER_REQUIRED', 'Chỉ chủ nhóm mới có thể thay đổi nhóm.'); }
        if ($action === 'rename') {
            $name = $input['name'] ?? null;
            if (!is_string($name)) { throw new HttpError(400, 'INVALID_NAME', 'Tên nhóm không hợp lệ.'); }
            $name = trim($name);
            if (mb_strlen($name) < 2 || mb_strlen($name) > 60 || preg_match('/[\x00-\x1f\x7f]/u', $name)) { throw new HttpError(400, 'INVALID_NAME', 'Tên nhóm cần từ 2 đến 60 ký tự.'); }
            try { $q = $this->db->prepare('UPDATE rooms SET name = ? WHERE id = ?'); $q->execute([$name, $roomId]); }
            catch (\PDOException) { throw new HttpError(409, 'NAME_EXISTS', 'Tên này đã được sử dụng. Hãy chọn tên khác.'); }
        } elseif ($action === 'add') {
            $target = $input['userId'] ?? null;
            if (!is_int($target) || $target < 1 || $target === $self) { throw new HttpError(400, 'INVALID_USER', 'Người được thêm không hợp lệ.'); }
            if (count($details['members']) >= 31) { throw new HttpError(400, 'GROUP_FULL', 'Nhóm đã đạt giới hạn 30 người được mời.'); }
            if (!$this->rows("SELECT 1 FROM friendships WHERE user_low = ? AND user_high = ? AND status = 'accepted'", [min($self, $target), max($self, $target)])) { throw new HttpError(400, 'NOT_FRIENDS', 'Chỉ có thể thêm người bạn đã kết nối.'); }
            $q = $this->db->prepare('INSERT OR IGNORE INTO group_members(room_id, user_id) VALUES (?, ?)'); $q->execute([$roomId, $target]);
            if (!$q->rowCount()) { throw new HttpError(409, 'ALREADY_MEMBER', 'Người này đã ở trong nhóm.'); }
        } elseif ($action === 'remove') {
            $target = $input['userId'] ?? null;
            if (!is_int($target) || $target < 1 || $target === (int)$details['ownerId']) { throw new HttpError(400, 'INVALID_USER', 'Không thể xóa chủ nhóm.'); }
            $q = $this->db->prepare('DELETE FROM group_members WHERE room_id = ? AND user_id = ?'); $q->execute([$roomId, $target]);
            if (!$q->rowCount()) { throw new HttpError(404, 'NOT_MEMBER', 'Người này không còn trong nhóm.'); }
        } elseif ($action === 'leave') {
            if ($owner) { throw new HttpError(400, 'OWNER_CANNOT_LEAVE', 'Chủ nhóm cần xóa nhóm hoặc chuyển quyền trước khi rời.'); }
            $q = $this->db->prepare('DELETE FROM group_members WHERE room_id = ? AND user_id = ?'); $q->execute([$roomId, $self]);
            return ['left' => true];
        } elseif ($action === 'delete') {
            if (!$owner) { throw new HttpError(403, 'OWNER_REQUIRED', 'Chỉ chủ nhóm mới có thể xóa nhóm.'); }
            $this->db->beginTransaction();
            try {
                foreach (['messages' => 'room_id', 'group_members' => 'room_id', 'private_groups' => 'room_id', 'rooms' => 'id'] as $table => $column) { $q = $this->db->prepare("DELETE FROM $table WHERE $column = ?"); $q->execute([$roomId]); }
                $this->db->commit();
            } catch (\Throwable $error) { $this->db->rollBack(); throw $error; }
            return ['deleted' => true];
        } else { throw new HttpError(400, 'INVALID_ACTION', 'Thao tác nhóm không hợp lệ.'); }
        return $this->groupDetails($self, $roomId);
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
