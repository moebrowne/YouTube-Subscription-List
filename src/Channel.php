<?php

declare(strict_types=1);

 class Channel
{
    public function __construct(
        public readonly string $url,
        public readonly bool $featured,
    ) {
    }

    public string $feedUrl {
        get {
            $cacheKey = hash('sha256', $this->url);
            $cacheFile = sys_get_temp_dir() . '/' . $cacheKey;

            if (file_exists($cacheFile)) {
                return file_get_contents($cacheFile);
            }

            $channelUrl = get_meta_tags($this->url)['twitter:url'] ?? throw new \InvalidArgumentException('Invalid channel URL: ' . $this->url);
            $channelId = str_replace('https://www.youtube.com/channel/', '', $channelUrl);
            $feedUrl = 'https://www.youtube.com/feeds/videos.xml?playlist_id=' . preg_replace('/^UC/', 'UULF', $channelId);

            file_put_contents($cacheFile, $feedUrl);

            return $feedUrl;
        }
    }
}