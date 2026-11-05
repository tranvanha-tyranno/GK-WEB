<?php
declare(strict_types=1);
namespace App\Http\Controllers;
use App\Application;
use App\Http\{HttpError, JsonResponse};

final class RoomController
{
    public function __construct(private readonly Application $app) {}
    public function all(int $userId): never { JsonResponse::data($this->app->rooms->all($userId)); }
    public function history(int $roomId, int $userId): never
    {
        if (!$this->app->rooms->canAccess($roomId, $userId)) { throw new HttpError(404, 'ROOM_NOT_FOUND', 'Phòng trò chuyện này không tồn tại.'); }
        $limit = $_GET['limit'] ?? '50';
        if (!is_string($limit) || !ctype_digit($limit) || (int) $limit < 1 || (int) $limit > 50) { throw new HttpError(400, 'INVALID_PAYLOAD', 'Số lượng tin nhắn cần lấy phải từ 1 đến 50.'); }
        $before = $_GET['beforeId'] ?? null;
        if ($before !== null && (!is_string($before) || !ctype_digit($before) || strlen($before) > 15 || (int)$before < 1)) { throw new HttpError(400, 'INVALID_PAYLOAD', 'Mốc lịch sử không hợp lệ.'); }
        JsonResponse::data($before === null ? $this->app->messages->latest($roomId, (int) $limit) : $this->app->messages->before($roomId, (int)$before, (int)$limit));
    }
    public function search(int $roomId, int $userId): never
    {
        if (!$this->app->rooms->canAccess($roomId, $userId)) { throw new HttpError(404, 'ROOM_NOT_FOUND', 'Phòng trò chuyện này không tồn tại.'); }
        $query = $_GET['q'] ?? '';
        $limit = $_GET['limit'] ?? '50';
        $before = $_GET['beforeId'] ?? null;
        if (!is_string($query) || mb_strlen($query) > 100 || !is_string($limit) || !ctype_digit($limit) || (int)$limit < 1 || (int)$limit > 50 || ($before !== null && (!is_string($before) || !ctype_digit($before) || strlen($before) > 15 || (int)$before < 1))) { throw new HttpError(400, 'INVALID_QUERY', 'Từ khóa tìm kiếm không hợp lệ.'); }
        $matches = $this->app->messages->search($roomId, trim($query), (int)$limit + 1, $before === null ? null : (int)$before);
        $hasMore = count($matches) > (int)$limit;
        if ($hasMore) { array_shift($matches); }
        JsonResponse::data(['messages' => $matches, 'hasMore' => $hasMore]);
    }
    public function poll(int $roomId, int $userId): never
    {
        if (!$this->app->config['benchmark']) { throw new HttpError(404, 'NOT_FOUND', 'Không tìm thấy nội dung yêu cầu.'); }
        if (!$this->app->rooms->canAccess($roomId, $userId)) { throw new HttpError(404, 'ROOM_NOT_FOUND', 'Phòng trò chuyện này không tồn tại.'); }
        $after = $_GET['afterId'] ?? '0';
        if (!is_string($after) || !ctype_digit($after) || strlen($after) > 15) { throw new HttpError(400, 'INVALID_PAYLOAD', 'Mốc tin nhắn không hợp lệ.'); }
        JsonResponse::data($this->app->messages->after($roomId, (int) $after));
    }
}
