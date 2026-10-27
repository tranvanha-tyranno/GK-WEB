<?php
declare(strict_types=1);
namespace App\Shared;

final class Protocol
{
    public static function encode(string $type, array $payload): string
    { return json_encode(['type' => $type, 'payload' => $payload], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR); }
}
