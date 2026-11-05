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
    public function logout(array $session): never
    {
        $this->app->sessions->revoke($session['id']);
        $this->setCookie(['cookie' => '', 'expires' => time() - 3600]);
        JsonResponse::data(['ok' => true]);
    }
}
