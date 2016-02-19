<?php

error_reporting(E_ALL);
ini_set('display_errors', true);

require 'subscriptions.class.php';
require 'channel.php';

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

$channelIDs = json_decode(file_get_contents('channels.json'));

$subscription = new \YouTubeSubscription\subscriptions($channelIDs);
$subscription->render();

foreach ($subscription->videos as $video) {
    echo '
    <div class="video">
        <header>'.$video->title.'</header>
        <p>'.date('d/m/Y', $video->timestamp).'</p>
        <a id="video_'.$video->ID.'" class="youtube" href="'.$video->URL.'"><img src="'.$video->thumbnail.'" /></a>
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
</script>
</body>
</html>
<html>
