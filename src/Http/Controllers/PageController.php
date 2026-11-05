<?php
declare(strict_types=1);
namespace App\Http\Controllers;

final class PageController
{
    public function render(string $page): never
    {
        header('Content-Type: text/html; charset=utf-8');
        header('Cache-Control: no-store');
        require dirname(__DIR__, 3) . '/resources/views/layout.php';
        exit;
    }
}
