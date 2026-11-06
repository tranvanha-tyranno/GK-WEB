<?php
declare(strict_types=1);
namespace App\Http;
use App\Application;
use App\Http\Controllers\{AuthController, RoomController, PageController, SocialController};
use App\Http\Middleware\{Csrf, RequireUser};

final class Router
{
    public function __construct(private readonly Application $app) {}
    public function run(): never
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if ($method === 'GET' && in_array($path, ['/', '/login', '/register', '/chat'], true)) {
            (new PageController())->render($path === '/chat' ? 'chat' : ($path === '/register' ? 'register' : 'login'));
        }
        if ($path === '/api/health' && $method === 'GET') {
            $this->app->db->query('SELECT 1');
            JsonResponse::data(['status' => 'ok']);
        }
        $session = $this->app->sessions->resolveCookie($_COOKIE['chat_session'] ?? null);
        $auth = new AuthController($this->app);
        if ($path === '/api/auth/me' && $method === 'GET') { $auth->me($session); }
        if ($method === 'POST') {
            Csrf::check($session, $_SERVER['HTTP_ORIGIN'] ?? '', $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '', $this->app->config['origins']);
            if (in_array($path, ['/api/auth/login', '/api/auth/register'], true)) {
                $contentType = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
                if ($contentType !== 'application/json') { throw new HttpError(400, 'INVALID_PAYLOAD', 'Yêu cầu phải dùng định dạng application/json.'); }
                $raw = file_get_contents('php://input', false, null, 0, 16385);
                if (strlen($raw) > 16384) { throw new HttpError(413, 'PAYLOAD_TOO_LARGE', 'Yêu cầu vượt quá kích thước cho phép.'); }
                try { $input = json_decode($raw, false, 16, JSON_THROW_ON_ERROR); }
                catch (\JsonException) { throw new HttpError(400, 'INVALID_PAYLOAD', 'Đối tượng JSON không hợp lệ.'); }
                if (!$input instanceof \stdClass) { throw new HttpError(400, 'INVALID_PAYLOAD', 'Dữ liệu phải là một đối tượng JSON.'); }
                if ($path === '/api/auth/login') { $auth->login((array)$input, $session); }
                $auth->register((array)$input, $session);
            }
            if ($path === '/api/auth/logout') { RequireUser::check($session); $auth->logout($session); }
        }
        RequireUser::check($session);
        $userId = $session['user']['id'];
        (new SocialController($this->app))->run((string)$path, $method, $userId);
        $rooms = new RoomController($this->app);
        if ($path === '/api/rooms' && $method === 'GET') { $rooms->all($userId); }
        if ($method === 'GET' && preg_match('#\A/api/rooms/([1-9][0-9]{0,8})/messages/search\z#D', (string)$path, $matches)) { $rooms->search((int)$matches[1], $userId); }
        if ($method === 'GET' && preg_match('#\A/api/rooms/([1-9][0-9]{0,8})/messages\z#D', (string) $path, $matches)) { $rooms->history((int) $matches[1], $userId); }
        if ($method === 'GET' && preg_match('#\A/api/benchmark/rooms/([1-9][0-9]{0,8})/messages\z#D', (string) $path, $matches)) { $rooms->poll((int) $matches[1], $userId); }
        throw new HttpError(404, 'NOT_FOUND', 'Không tìm thấy nội dung yêu cầu.');
    }
}
