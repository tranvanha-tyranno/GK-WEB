<?php
declare(strict_types=1);
namespace App\Http\Middleware;
use App\Http\HttpError;

final class RequireUser
{
    public static function check(?array $session): array
    {
        if ($session === null || $session['user'] === null) { throw new HttpError(401, 'UNAUTHORIZED', 'Vui lòng đăng nhập để tiếp tục.'); }
        return $session['user'];
    }
}
