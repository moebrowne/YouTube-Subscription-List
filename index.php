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
<link rel="stylesheet" href="node_modules/jquery-colorbox/example3/colorbox.css" type="text/css">
<script src="node_modules/masonry-layout/dist/masonry.pkgd.min.js"></script>
<style type="text/css">

body, html {
margin: 0;
padding: 0;
font-family: Ubuntu;
}

.videoGrid {
    width: 1100px;
    margin: 0 auto;
}

.videoGridgrid:after {
  content: '';
  display: block;
  clear: both;
}

/* ---- grid-item ---- */

.videoTile {
  width: calc(50% - 10px);
  height: 250px;
  float: left;
}

.videoTile.videoTile--width1 {
    width: calc(8.3% - 10px);
    height: 200px;
}

.videoTile.videoTile--width2 {
    width: calc(16.6% - 10px);
    height: 250px;
}

.videoTile.videoTile--width4 {
    width: calc(33.3% - 10px);
    height: 400px;
}

.videoTile.videoTile--width6 {
    width: calc(50% - 10px);
    height: 510px;
}

.videoTile.videoTile--width8 {
    width: calc(66.6% - 10px);
    height: 450px;
}

.videoTile.videoTile--width12 {
    width: 100%;
    height: 600px;
}

img {
    width: 100%;
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
    $arr = [2, 2, 2, 2, 2, 6, 6];
    echo '
    <div class="videoTile videoTile--width'.$arr[array_rand($arr)].'">
        <a id="video_'.$video->ID.'" class="youtube" href="'.$video->URL.'"><img src="'.$video->thumbnail.'" /></a>
        <header>'.$video->title.'</header>
        <p>'.date('d/m/Y', $video->timestamp).'</p>
    </div>
    ';
}

?>
</div>
<script>
$("a.youtube").colorbox({iframe:true, width:'100%', height:'100%'});

$("a").on('click','.youtube', function(e) {
    var watched = localStorage['watched'][$(this).attr('id')] = true;
});

var $grid = $('.videoGrid').masonry({
    // options
    itemSelector: '.videoTile',
    columnWidth: 91.66
});
</script>
</body>
</html>
<html>
