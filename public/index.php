<?php

declare(strict_types=1);

foreach (glob(__DIR__ . '/../src/*.php') as $path) {
    require $path;
}

$channels = new ChannelCollection(__DIR__ . '/../channels.txt');
$videos = new VideoCollection(__DIR__ . '/../videos.json');
$videos->add(...VideoFetcher::run($channels));

$videos = $videos->toArray();

$totalVideos   = count($videos);
$totalChannels = count($channels);
$watchedCount  = count(array_filter($videos, fn(Video $v): bool => $v->watched));

$videos = array_splice($videos, 0, 300);

function e(?string $value): string {
    return htmlentities($value ?? '');
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <title>Subscriptions</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            font-family: Ubuntu, sans-serif;
            background: #f8f8f8;
        }

        yt-videos {
            display: grid;
            grid-gap: 10px;
            grid-auto-flow: dense;
            grid-template-columns: repeat(auto-fill, calc(20vw - 8px));
        }

        yt-video[featured]:not([watched]) {
            grid-column-end: span 2;
            grid-row-end: span 2;
            height: calc((40vw - (10px/2)) * (720 / 1280));
            max-height: 100%;
        }

        yt-video {
            position: relative;
            height: calc((20vw - 10px) * (720 / 1280));
            max-height: 100%;
            background-color: rgba(51, 51, 51, 0.23);
            cursor: pointer;
        }

        yt-video header {
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

        yt-video[watched] {
            opacity: 0.3;
            filter: grayscale(0.8);
        }

        img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        dialog {
            padding: 0;
            border: none;
            width: 100vw;
            height: 100vh;
            max-width: 100vw;
            max-height: 100vh;
            background: rgba(21, 21, 21, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        dialog::backdrop {
            background: rgba(0, 0, 0, 0.8);
        }

        dialog iframe {
            width: calc(100vh * (16 / 9));
            height: 100vh;
            max-width: 100vw;
            border: none;
        }

        .close-button {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            border: 2px solid white;
            font-size: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }

        .close-button:hover {
            background: rgba(255, 0, 0, 0.7);
        }

        #last-updated {
            position: sticky;
            top: 0;
            z-index: 1;
            padding: 6px 10px;
            font-size: 11px;
            color: rgba(0, 0, 0, 0.45);
        }
    </style>
</head>
<body>
<div id="last-updated"><?= number_format($totalVideos) ?> videos from <?= number_format($totalChannels) ?> channels, <?= number_format($watchedCount) ?> watched &mdash; Updated <?= date('D j M, H:i') ?></div>
<yt-videos>
    <?php foreach ($videos as $video) : ?>
        <yt-video
            video-id="<?= e($video->id) ?>"
            title="<?= e($video->title) ?>"
            <?= $video->featured ? 'featured' : '' ?>
            <?= $video->watched ? 'watched' : '' ?>
            embed-url="<?= e($video->embedUrl) ?>"
        >
            <img src="/thumbnail.php?id=<?= e($video->id) ?>" loading="lazy" width="1280" height="720" />
            <header><?= e($video->title) ?></header>
        </yt-video>
    <?php endforeach; ?>
</yt-videos>

<dialog id="video-dialog">
    <button class="close-button" id="close-dialog">✕</button>
    <iframe
        id="video-iframe"
        src="about:blank"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
        allowfullscreen>
    </iframe>
</dialog>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dialog = document.getElementById('video-dialog');
        const videoIframe = document.getElementById('video-iframe');

        const markVideoAsWatched = (videoId) => {
            document
                .querySelector(`yt-video[video-id="${videoId}"]`)
                ?.setAttribute('watched', '');

            fetch('/watched.php', {method: 'POST', body: videoId});
        };

        document.querySelector('yt-videos')
            .addEventListener('click', function(event) {
                const videoElement = event.target.closest('yt-video');

                if (videoElement === null) {
                    return;
                }

                const videoId = videoElement.getAttribute('video-id');

                markVideoAsWatched(videoId);

                videoIframe.src = videoElement.getAttribute('embed-url');
                videoIframe.title = videoElement.getAttribute('title');

                dialog.showModal();
            });

        document
            .getElementById('close-dialog')
            .addEventListener('click', () => {
                dialog.close();
            });

        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                dialog.close();
            }
        });

        dialog.addEventListener('close', () => {
            videoIframe.src = 'about:blank';
        });

        const loadedAt = Date.now();

        setInterval(
            () => {
                const refreshIsDue = Date.now() - loadedAt > 60 * 60 * 1000;
                const playerIsOpen = dialog.open === false;
                const tabIsActive = document.visibilityState === 'visible';

                if (refreshIsDue === false || playerIsOpen || tabIsActive) {
                    return;
                }

                location.reload();
            },
            60 * 1000
        );
    });
</script>
</body>
</html>