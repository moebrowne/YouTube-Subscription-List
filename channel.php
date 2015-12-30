<?php namespace YouTubeSubscription;

class channel
{

    private $ID;
    private $cacheDir = './cache';
    private $cachePath;
    private $defaultExpiry;

    private $headers;
    public $XML;
    private $expires;

    /**
     * Initiate
     * @param string $ID The ID of the channel
     */
    public function __construct($ID)
    {
        if(is_dir($this->cacheDir) === false) {
            mkdir($this->cacheDir);
        }

        $this->defaultExpiry = (60*60*15); // 15 minutes

        $this->ID = $ID;
        $this->cachePath = $this->cacheDir.'/'.$this->ID.'.json';
        $this->getXML();
    }

    private function getHeaders()
    {
        if($this->headers !== null) {
            return $this->headers;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.youtube.com/feeds/videos.xml?channel_id='.$this->ID);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true); // HEAD request

        $XMLHeaders = curl_exec($ch);

        // Cache the headers
        $this->headers = $XMLHeaders;

        return $XMLHeaders;
    }

    private function getExpires()
    {
        if($this->expires !== null) {
            return $this->expires;
        }

        // Get the headers
        $headers = $this->getHeaders();

        // Get the returned headers from the request
        preg_match('/^Expires: (?<expires>.+)$/m', $headers, $matches);

        if ($matches['expires'] !== null) {
            $expiryDate = $matches['expires'];

            // Convert to a timestamp
            $expiryTimestamp = strtotime($expiryDate);

            $this->expires = $expiryTimestamp;

            return $expiryTimestamp;
        }

        // If we couldn't find a timestamp create a default one
        $defaultExpiry = $this->defaultExpiry + time();

        $this->expires = $defaultExpiry;

        return $defaultExpiry;

    }

    private function getXML()
    {
        // Load from cache if there is one
        if ($this->XML === null && $this->isCached()) {
            $this->getCache();
        }

        // Check if the XML we have is OK to use
        if ($this->XML !== null && $this->isExpired() === false) {
            return $this->XML;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://www.youtube.com/feeds/videos.xml?channel_id='.$this->ID);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);

        $XMLRaw = curl_exec($ch);

        $XMLHeaderSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $XMLHeader = substr($XMLRaw, 0, $XMLHeaderSize);
        $XMLBody = substr($XMLRaw, $XMLHeaderSize);

        $this->XML = $XMLBody;
        $this->headers = $XMLHeader;

        return $XMLBody;
    }

    private function isCached()
    {
        return (bool)file_exists($this->cachePath);
    }

    private function getCache()
    {
        $cachedJSON = file_get_contents($this->cachePath);

        $data = json_decode($cachedJSON);

        $this->XML = $data->data;
        $this->expires = $data->expires;

        return $data->data;
    }

    private function isExpired()
    {
        return (bool)($this->getExpires() <= time());
    }

}