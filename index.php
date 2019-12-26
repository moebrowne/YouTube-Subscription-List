<?php

use YouTubeSubscription\subscriptions;

error_reporting(E_ALL);
ini_set('display_errors', true);

require 'subscriptions.class.php';
require 'channel.php';
?>
<!doctype html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<title>Subscriptions</title>
<script src="node_modules/jquery/dist/jquery.min.js"></script>
<script src="node_modules/jquery-colorbox/jquery.colorbox-min.js"></script>
<script src="node_modules/loading-attribute-polyfill/loading-attribute-polyfill.min.js"></script>
<link rel="stylesheet" href="node_modules/jquery-colorbox/example3/colorbox.css" type="text/css">
<style type="text/css">

body, html {
    margin: 0;
    padding: 0;
    font-family: Ubuntu;
}

.videoGrid {
    display: grid;
    grid-gap: 10px;
    grid-auto-flow: dense;
    grid-template-columns: repeat(auto-fill, calc(20vw - 11px));
}

.videoFeatured:not(.watched) {
    grid-column-end: span 2;
    grid-row-end: span 2;
    height: calc((40vw - (10px/2)) * (720 / 1280));
    max-height: 100%;
}

.videoTile {
    position: relative;
    height: calc((20vw - 10px) * (720 / 1280));
    max-height: 100%;
}

.videoTile header {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 5px;
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
    font-size: 12px;
    color: #FFF;
    background: rgba(0,0,0,0.75);
}

.videoTile.watched {
    opacity: 0.3;
}

img {
    display: block;
    width: 100%;
    height: 100%;
}

</style>
</head>
<body>
<div class="videoGrid">
<?php

$channelIDs = json_decode(file_get_contents('channels.json'));

$subscription = new subscriptions($channelIDs);
$subscription->render();

foreach ($subscription->videos as $video) {
    echo '
    <div class="videoTile ' . ($video->featured ? 'videoFeatured':'') . '" title="'.$video->title.'">
        <a id="video_'.$video->ID.'" class="youtube" href="'.$video->URL.'?autoplay=1">
            <noscript class="loading-lazy">
                <img src="'.$video->thumbnail.'" loading="lazy" width="1280" height="720" />
            </noscript>
        </a>
        <header>'.$video->title.'</header>
    </div>
    ';
}

?>
</div>
<script>
$("a.youtube").colorbox({iframe:true, width:'100%', height:'100%'});

$("a.youtube").on('click', function(e) {
    var watched = JSON.parse(localStorage.getItem('watched')) || [];

    watched.push($(this).attr('id'));

    localStorage.setItem('watched', JSON.stringify(watched));

    markWatchedVideos();
});

function markWatchedVideos() {
    var watched = JSON.parse(localStorage.getItem('watched')) || []

    watched.forEach(function(ID) {
        $('#' + ID).parent().addClass('watched')
    })
}

markWatchedVideos();

</script>
</body>
</html>
