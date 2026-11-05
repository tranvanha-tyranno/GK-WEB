<?php
declare(strict_types=1);
namespace App\Http\Controllers;
use App\Application;
use App\Auth\Password;
use App\Http\{HttpError, JsonResponse};
use App\Repositories\UserRepository;

final class AuthController
{
    public function __construct(private readonly Application $app) {}
    private function setCookie(array $issued): void
    {
        setcookie('chat_session', $issued['cookie'], [
            'expires' => $issued['expires'], 'path' => '/', 'httponly' => true,
            'secure' => str_starts_with($this->app->config['url'], 'https://'), 'samesite' => 'Lax',
        ]);
    }
    public function me(?array $session): never
    {
        if ($session === null) {
            $issued = $this->app->sessions->issue();
            $this->setCookie($issued);
            JsonResponse::data(['user' => null, 'csrfToken' => $issued['csrfToken']]);
        }
        JsonResponse::data(['user' => $session['user'], 'csrfToken' => $session['csrfToken']]);
    }
    public function login(array $input, array $session): never
    {
        $username = $input['username'] ?? null;
        $password = $input['password'] ?? null;
        if (!is_string($username) || !is_string($password) || strlen($username) > 64 || strlen($password) > 256) {
            throw new HttpError(400, 'INVALID_PAYLOAD', 'Vui lòng nhập tên đăng nhập và mật khẩu hợp lệ.');
        }
        $user = $this->app->users->findByUsername(trim($username));
        // A fixed valid dummy hash prevents skipping the expensive verification for unknown accounts.
        $hash = $user['password_hash'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.';
        if (!Password::verify($password, $hash) || $user === null) { throw new HttpError(401, 'INVALID_CREDENTIALS', 'Tên đăng nhập hoặc mật khẩu không đúng.'); }
        $issued = $this->app->sessions->issue((int) $user['id'], $session['id']);
        $this->setCookie($issued);
        JsonResponse::data(['user' => UserRepository::present($user), 'csrfToken' => $issued['csrfToken']]);
    }
    public function register(array $input, array $session): never
    {
        $username = $input['username'] ?? null;
        $displayName = $input['displayName'] ?? null;
        $password = $input['password'] ?? null;
        if (!is_string($username) || !is_string($displayName) || !is_string($password)) { throw new HttpError(400, 'INVALID_PAYLOAD', 'Vui lòng điền đầy đủ thông tin tài khoản.'); }
        $username = strtolower(trim($username)); $displayName = trim($displayName);
        if (!preg_match('/\A[a-z0-9][a-z0-9._-]{2,31}\z/D', $username)) { throw new HttpError(400, 'INVALID_USERNAME', 'Tên đăng nhập cần 3–32 ký tự: chữ thường, số, dấu chấm, gạch ngang hoặc gạch dưới.'); }
        if (mb_strlen($displayName) < 2 || mb_strlen($displayName) > 60 || preg_match('/[\x00-\x1f\x7f]/u', $displayName)) { throw new HttpError(400, 'INVALID_DISPLAY_NAME', 'Tên hiển thị cần từ 2 đến 60 ký tự.'); }
        if (strlen($password) < 12 || strlen($password) > 256) { throw new HttpError(400, 'INVALID_PASSWORD', 'Mật khẩu cần từ 12 đến 256 byte.'); }
        if ($this->app->users->findByUsername($username) !== null) { throw new HttpError(409, 'USERNAME_EXISTS', 'Tên đăng nhập đã được sử dụng. Hãy chọn tên khác.'); }
        try { $id = $this->app->users->create($username, $displayName, Password::hash($password)); }
        catch (\PDOException $error) { throw new HttpError(409, 'USERNAME_EXISTS', 'Tên đăng nhập đã được sử dụng. Hãy chọn tên khác.'); }
        $user = $this->app->users->findByUsername($username);
        if ($user === null) { throw new HttpError(503, 'REGISTRATION_FAILED', 'Không thể tạo tài khoản. Vui lòng thử lại.'); }
        $issued = $this->app->sessions->issue($id, $session['id']); $this->setCookie($issued);
        JsonResponse::data(['user' => UserRepository::present($user), 'csrfToken' => $issued['csrfToken']], 201);
    }
    public function logout(array $session): never
    {
        $this->app->sessions->revoke($session['id']);
        $this->setCookie(['cookie' => '', 'expires' => time() - 3600]);
        JsonResponse::data(['ok' => true]);
    }
}
