<?php
declare(strict_types=1);

namespace App\Database;

use PDO;

final class Connection
{
    public static function open(?string $path = null): PDO
    {
        $path ??= (string) (getenv('DATABASE_PATH') ?: dirname(__DIR__, 2) . '/storage/chat.sqlite');
        if ($path !== ':memory:' && !is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }
        $db = new PDO('sqlite:' . $path, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $db->exec('PRAGMA foreign_keys = ON; PRAGMA busy_timeout = 3000; PRAGMA journal_mode = WAL;');
        return $db;
    }
}
