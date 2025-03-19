<?php

declare(strict_types=1);

class VideoFetcher
{
    private array $curlHandles = [];

    public function __construct(
        private readonly array $channels,
    ) {
    }

    public function run(): array
    {
        $channelUrls = array_map(
            fn (Channel $channel): string => 'https://www.youtube.com/feeds/videos.xml?channel_id=UC' . $channel->id,
            $this->channels,
        );

        $multiHandle = curl_multi_init();
        curl_multi_setopt($multiHandle, CURLMOPT_MAX_HOST_CONNECTIONS, 1);

        $shareHandle = curl_share_init();
        curl_share_setopt($shareHandle, CURLSHOPT_SHARE, CURL_LOCK_DATA_DNS);
        curl_share_setopt($shareHandle, CURLSHOPT_SHARE, CURL_LOCK_DATA_SSL_SESSION);
        curl_share_setopt($shareHandle, CURLSHOPT_SHARE, CURL_LOCK_DATA_CONNECT);

        foreach ($channelUrls as $channelUrl) {
            $curlHandle = curl_init();

            curl_setopt_array($curlHandle, [
                CURLOPT_URL => $channelUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SHARE => $shareHandle,
                CURLOPT_USERAGENT => 'RSS Feed Reader/1.0',
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_ENCODING => 'gzip',
            ]);

            curl_multi_add_handle($multiHandle, $curlHandle);

            $this->curlHandles[] = $curlHandle;
        }

        do {
            $status = curl_multi_exec($multiHandle, $pendingRequests);

            if (curl_multi_select($multiHandle, 0.1) === -1) {
                usleep(5_000);
            }
        } while ($pendingRequests > 0 && $status === CURLM_OK);

        $videos = [];

        foreach ($this->curlHandles as $handle) {
            $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            $content = curl_multi_getcontent($handle);

            if ($httpCode === 200) {
                $videos = [...$videos, ...$this->parseYouTubeXml($content)];
            }

            curl_multi_remove_handle($multiHandle, $handle);
            curl_close($handle);
        }

        curl_multi_close($multiHandle);
        curl_share_close($shareHandle);

        usort($videos, static fn(Video $a, Video $b): int => $b->publishedAt <=> $a->publishedAt);

        return $videos;
    }

    /** @return Video[] */
    private function parseYouTubeXml(string $xmlContent): array
    {
        $xml = new SimpleXMLElement($xmlContent);
        $namespaces = $xml->getNamespaces(true);

        $videos = [];

        foreach ($xml->entry as $entry) {
            $mediaGroup = $entry->children($namespaces['media'])->group;

            $videoId = (string)$entry->children($namespaces['yt'])->videoId;

            $videos[$videoId] = new Video(
                $videoId,
                (string)$xml->children($namespaces['yt'])->channelId,
                (string)$entry->title,
                new DateTimeImmutable((string)$entry->published),
                (string)$mediaGroup->children($namespaces['media'])->description
            );
        }

        return $videos;
    }
}
