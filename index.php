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
    width: 100%;
    margin: 0 auto;
    display: grid;
    grid-gap: 10px;
    grid-auto-flow: dense;
    grid-template-columns: repeat(auto-fill, 200px);
    grid-auto-rows: 200px;
}

.videoFeatured {
    grid-column-end: span 2;
    grid-row-end: span 2;
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
    echo '
    <div class="videoTile ' . (mt_rand(0, 100) > 80 ? 'videoFeatured':'') . '">
        <a id="video_'.$video->ID.'" class="youtube" href="'.$video->URL.'">
            <img src="'.$video->thumbnail.'" />
        </a>
        <header>'.$video->title.'</header>
        <p>'.date('d/m/Y', $video->timestamp).'</p>
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
});
</script>
</body>
</html>