<?php

echo '<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<title>Subscriptions</title>
<script src="node_modules/jquery/dist/jquery.min.js"></script>
<script src="node_modules/jquery-colorbox/jquery.colorbox-min.js"></script>
<link rel="stylesheet" href="node_modules/jquery-colorbox/example3/colorbox.css" type="text/css">
<style type="text/css">

body, html {
margin: 0;
padding: 0;
}

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

$dirCache = './cache';

$channelIDs = json_decode(file_get_contents('channels.json'));

if(is_dir($dirCache) === false) {
    mkdir($dirCache);
}

foreach ($channelIDs as $channelID) {
    if(file_exists($dirCache.'/'.$channelID) === false) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.youtube.com/feeds/videos.xml?channel_id='.$channelID);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);

        $XMLRaw = curl_exec($ch);

        $XMLHeaderSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $XMLHeader = substr($XMLRaw, 0, $XMLHeaderSize);
        $XMLBody = substr($XMLRaw, $XMLHeaderSize);

        // Get the returned headers from the request
        preg_match('/^Expires: (?<expires>.+)$/m', $XMLHeader, $matches);

        if ($matches['expires'] !== null) {
            $expiryDate = $matches['expires'];

            // Convert to a timestamp
            $expiryTimestamp = strtotime($expiryDate);
        }

        file_put_contents($dirCache.'/'.$channelID, $XMLBody);
        break;
    }
    else {
        $XML = file_get_contents($dirCache.'/'.$channelID);
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
            'URL' => 'https://www.youtube.com/embed/'.$YTID,
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
        <a class="youtube" href="'.$video->URL.'"><img src="'.$video->thumbnail.'" /></a>
    </div>
    ';
}

?>
</div>
<script>
$("a.youtube").colorbox({iframe:true, width:'100%', height:'100%'});
</script>
</body>
</html>
<html>
