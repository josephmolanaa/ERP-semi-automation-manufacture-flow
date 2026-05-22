<?php

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$file = __DIR__ . $path;

if ($path === '/health') {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok']);
    return true;
}

if ($path !== '/' && is_file($file)) {
    return false;
}

require __DIR__ . '/index.php';
