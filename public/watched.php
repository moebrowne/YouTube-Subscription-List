<?php

declare(strict_types=1);

foreach (glob(__DIR__ . '/../src/*.php') as $path) {
    require $path;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$id = trim(file_get_contents('php://input'));

if (!is_string($id) || $id === '') {
    http_response_code(400);
    exit;
}

$videos = new VideoCollection(__DIR__ . '/../videos.json');

if (!isset($videos[$id])) {
    http_response_code(404);
    exit;
}

$videos[$id]->watched = true;
$videos->save();

http_response_code(204);
