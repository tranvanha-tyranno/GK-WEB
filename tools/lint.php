<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$paths = ['src', 'public', 'config', 'bootstrap', 'bin', 'database', 'tests', 'resources', 'tools'];
$count = 0;
foreach ($paths as $path) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/' . $path, FilesystemIterator::SKIP_DOTS));
    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') { continue; }
        exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($file->getPathname()), $output, $code);
        if ($code !== 0) { echo implode("\n", $output); exit($code); }
        $output = []; $count++;
    }
}
echo "Syntax valid: $count PHP files.\n";
