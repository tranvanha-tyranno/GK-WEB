<?php
declare(strict_types=1);
namespace App\Http;

final class JsonResponse
{
    public static function send(array $body, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        exit;
    }
    public static function data(mixed $data, int $status = 200): never { self::send(['data' => $data], $status); }
}
