<?php
declare(strict_types=1);

$env = static fn (string $key, string $default = ''): string => (string) (getenv($key) === false ? $default : getenv($key));
return [
    'env' => $env('APP_ENV', 'local'),
    'url' => rtrim($env('APP_URL', 'http://localhost:8080'), '/'),
    'database' => $env('DATABASE_PATH', dirname(__DIR__) . '/storage/chat.sqlite'),
    'secret' => $env('SESSION_SECRET'),
    'origins' => array_values(array_filter(array_map('trim', explode(',', $env('ORIGIN_ALLOWLIST', 'http://localhost:8080'))))),
    'wsHost' => $env('WS_HOST', '0.0.0.0'),
    'wsPort' => (int) $env('WS_PORT', '8081'),
    'benchmark' => $env('BENCHMARK_ENABLED', '0') === '1',
];
