<?php namespace YouTubeSubscription;

class channel
{

    private $ID;
    private $cacheDir = './cache';
    private $defaultExpiry = (60*60*15); // 15 minutes

    /**
     * Initiate
     * @param string $ID The ID of the channel
     */
    public function __construct($ID)
    {
        $this->ID = $ID;
    }

    private function getHeaders()
    {

    }

    public function download()
    {

    }

    private function isCached()
    {
        return (bool)file_exists($this->cacheDir.'/'.$this->ID.'.json');
    }

    private function isExpired()
    {

    }

}