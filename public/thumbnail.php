<?php

declare(strict_types=1);

$videoId = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($videoId)) {
    header("HTTP/1.0 400 Bad Request");
    die("Error: Missing YouTube video ID");
}

$thumbnailTypes = [
    'maxresdefault.jpg', // 1280x720
//    'sddefault.jpg',     // 640x480
//    'hqdefault.jpg',     // 480x360
    'mqdefault.jpg',     // 320x180
//    'default.jpg'        // 120x90
];

$baseUrl = "https://i4.ytimg.com/vi/{$videoId}/";
$foundThumbnail = false;

foreach ($thumbnailTypes as $type) {
    $thumbnailUrl = $baseUrl . $type;

    $ch = curl_init($thumbnailUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_NOBODY => true,
        CURLOPT_TIMEOUT => 5
    ]);

    curl_exec($ch);

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $contentLength = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
    curl_close($ch);

    if ($httpCode == 200 && $contentLength > 4000) {
        $foundThumbnail = true;

        $ch = curl_init($thumbnailUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10
        ]);

        $imageData = curl_exec($ch);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        header("Content-Type: {$contentType}");
        header("Content-Length: " . strlen($imageData));
        header("Cache-Control: public, max-age=86400");
        echo $imageData;
        exit;
    }
}

header("HTTP/1.0 404 Not Found");

die("Error: No suitable thumbnail found for video ID: {$videoId}");
