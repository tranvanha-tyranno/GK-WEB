<?php
declare(strict_types=1);
namespace App\Http\Middleware;
use App\Http\HttpError;

final class Csrf
{
    public static function check(?array $session, string $origin, string $token, array $allowed): void
    {
        if (!in_array($origin, $allowed, true) || $session === null || !hash_equals($session['csrfToken'], $token)) {
            throw new HttpError(403, 'FORBIDDEN', 'Kiểm tra bảo mật không thành công. Hãy tải lại trang rồi thử lại.');
        }
    }
}
