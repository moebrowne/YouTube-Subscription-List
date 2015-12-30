<?php namespace YouTubeSubscription;

class channel
{

    private $ID;
    private $cacheDir = './cache';
    private $cachePath;
    private $defaultExpiry = (60*60*15); // 15 minutes

    private $data;
    private $expires;

    /**
     * Initiate
     * @param string $ID The ID of the channel
     */
    public function __construct($ID)
    {
        $this->ID = $ID;
        $this->cachePath = $this->cacheDir.'/'.$this->ID.'.json';
    }

    private function getHeaders()
    {

    }

    public function download()
    {

    }

    private function isCached()
    {
        return (bool)file_exists($this->cachePath);
    }

    private function isExpired()
    {

    }

}