<?php

declare(strict_types=1);

class VideoFetcher
{
    public static function run(ChannelCollection $channels): array
    {
        $multiHandle = curl_multi_init();
        curl_multi_setopt($multiHandle, CURLMOPT_MAX_HOST_CONNECTIONS, 1);

        $shareHandle = curl_share_init();
        curl_share_setopt($shareHandle, CURLSHOPT_SHARE, CURL_LOCK_DATA_DNS);
        curl_share_setopt($shareHandle, CURLSHOPT_SHARE, CURL_LOCK_DATA_SSL_SESSION);
        curl_share_setopt($shareHandle, CURLSHOPT_SHARE, CURL_LOCK_DATA_CONNECT);

        $curlHandles = new WeakMap();

        foreach ($channels as $channel) {
            $curlHandle = curl_init();

            curl_setopt_array($curlHandle, [
                CURLOPT_URL => $channel->feedUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SHARE => $shareHandle,
                CURLOPT_USERAGENT => 'RSS Feed Reader/1.0',
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_2_0,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_ENCODING => 'gzip',
            ]);

            curl_multi_add_handle($multiHandle, $curlHandle);

            $curlHandles[$channel] = $curlHandle;
        }

        do {
            $status = curl_multi_exec($multiHandle, $pendingRequests);

            if (curl_multi_select($multiHandle, 0.1) === -1) {
                usleep(5_000);
            }
        } while ($pendingRequests > 0 && $status === CURLM_OK);

        $videos = [];

        foreach ($curlHandles as $channel => $handle) {
            $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            $content = curl_multi_getcontent($handle);

            if ($httpCode === 200) {
                $videos = [...$videos, ...self::parseYouTubeXml($content, $channel->featured)];
            }

            curl_multi_remove_handle($multiHandle, $handle);
            curl_close($handle);
        }

        curl_multi_close($multiHandle);
        curl_share_close($shareHandle);

        return $videos;
    }

    /** @return Video[] */
    private static function parseYouTubeXml(string $xmlContent, bool $videosAreFeatured): array
    {
        $xml = new SimpleXMLElement($xmlContent);
        $namespaces = $xml->getNamespaces(true);

        $videos = [];

        foreach ($xml->entry as $entry) {
            $mediaGroup = $entry->children($namespaces['media'])->group;

            $videoId = (string)$entry->children($namespaces['yt'])->videoId;

            $videos[$videoId] = new Video(
                $videoId,
                (string)$entry->title,
                new DateTimeImmutable((string)$entry->published),
                (string)$mediaGroup->children($namespaces['media'])->description,
                $videosAreFeatured,
            );
        }

        return $videos;
    }
}
