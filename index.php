<?php

echo '<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<title>Subscriptions</title>
<style type="text/css">
.video {
    margin: 1.5%;
    flex: 1 1 20%;
}

.video header {
    font-size: 16px;
}

img {
    width: 100%;
}
</style>
</head>
<body>
<div style="display: flex; flex-wrap: wrap; list-style: outside none none;">
';

$channelIDs = [
    'UCtESv1e7ntJaLJYKIO1FoYw',
    'UC9CuvdOVfMPvKCiwdGKL3cQ',
];

foreach ($channelIDs as $channelID) {
    if(file_exists($channelID) === false) {
        $XML = file_get_contents('https://www.youtube.com/feeds/videos.xml?channel_id='.$channelID);
        file_put_contents($channelID,$XML);
    }
    else {
        $XML = file_get_contents($channelID);
        }

    $channel = new SimpleXMLElement($XML);

    foreach ($channel->entry as $video) {

        //var_dump($video->published);

        $media = $video->children('http://search.yahoo.com/mrss/')->group;
        $YTID = (string)$video->children('http://www.youtube.com/xml/schemas/2015')->videoId;

        $timestamp = DateTime::createFromFormat('Y-m-d\TH:i:sP', (string)$video->published)->getTimestamp();

        $videoData[(int)$timestamp] = (object)[
            'title' => $video->title,
            'date' => (string)$video->published,
            'timestamp' => $timestamp,
            'URL' => 'https://www.youtube.com/watch?v='.$YTID,
            'thumbnail' => (string)$media->thumbnail->attributes()->url,
        ];
    }
}

krsort($videoData, SORT_NUMERIC);

foreach ($videoData as $video) {
    echo '
    <div class="video">
        <header>'.$video->title.'</header>
        <p>'.date('d/m/Y', $video->timestamp).'</p>
        <img src="'.$video->thumbnail.'" />
    </div>
    ';
}

echo '
</div>
</body>
</html>
<html>';

