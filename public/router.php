<?php

declare(strict_types=1);

// Для встроенного сервера PHP: php -S localhost:8000 -t public public/router.php

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$file = __DIR__ . $path;

if ($path !== '/' && is_file($file)) {
    return false;
}

if (is_dir($file)) {
    $index = rtrim($file, '/') . '/index.html';

    if (is_file($index)) {
        if (!str_ends_with($path, '/')) {
            header('Location: ' . rtrim($path, '/') . '/', true, 302);
            return true;
        }

        header('Content-Type: text/html; charset=utf-8');
        readfile($index);
        return true;
    }
}

require __DIR__ . '/index.php';
