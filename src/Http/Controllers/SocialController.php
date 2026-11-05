<?php
declare(strict_types=1);
namespace App\Http\Controllers;
use App\Application;
use App\Http\{HttpError, JsonResponse};
use App\Repositories\SocialRepository;

final class SocialController
{
    public function __construct(private readonly Application $app) {}
    public function run(string $path, string $method, int $self): void
    {
        $social = new SocialRepository($this->app->db);
        if ($path === '/api/people' && $method === 'GET') {
            $mode = $_GET['mode'] ?? 'all'; $search = $_GET['q'] ?? '';
            if (!is_string($mode) || !in_array($mode, ['all', 'friends', 'requests', 'suggestions', 'nearby', 'random'], true) || !is_string($search) || mb_strlen($search) > 100) { throw new HttpError(400, 'INVALID_QUERY', 'Bộ lọc không hợp lệ.'); }
            JsonResponse::data($social->people($self, $mode, trim($search)));
        }
        if ($method !== 'POST' || !in_array($path, ['/api/friends', '/api/location', '/api/groups'], true)) { return; }
        if (strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0])) !== 'application/json') { throw new HttpError(400, 'INVALID_PAYLOAD', 'Yêu cầu phải dùng JSON.'); }
        $raw = file_get_contents('php://input', false, null, 0, 16385);
        if (strlen($raw) > 16384) { throw new HttpError(413, 'PAYLOAD_TOO_LARGE', 'Yêu cầu quá lớn.'); }
        try { $input = json_decode($raw, false, 16, JSON_THROW_ON_ERROR); }
        catch (\JsonException) { throw new HttpError(400, 'INVALID_PAYLOAD', 'JSON không hợp lệ.'); }
        if (!$input instanceof \stdClass) { throw new HttpError(400, 'INVALID_PAYLOAD', 'Dữ liệu không hợp lệ.'); }
        $input = (array)$input;
        if ($path === '/api/friends') {
            if (!is_int($input['userId'] ?? null) || !is_string($input['action'] ?? null)) { throw new HttpError(400, 'INVALID_PAYLOAD', 'Lời mời không hợp lệ.'); }
            $social->friendship($self, $input['userId'], $input['action']);
        } elseif ($path === '/api/location') { $social->location($self, $input); }
        else {
            if (!is_string($input['name'] ?? null) || !is_array($input['memberIds'] ?? null) || !array_is_list($input['memberIds'])) { throw new HttpError(400, 'INVALID_PAYLOAD', 'Thông tin nhóm không hợp lệ.'); }
            JsonResponse::data($social->createGroup($self, $input['name'], $input['memberIds']));
        }
        JsonResponse::data(['ok' => true]);
    }
}
