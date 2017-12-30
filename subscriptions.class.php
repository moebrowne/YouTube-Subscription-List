<?php namespace YouTubeSubscription;

use DateTime;
use SimpleXMLElement;

class subscriptions {

    public $channelIDs = [];
    public $channels = [];
    public $videos = [];

    /**
     * Initiate!
     * @param array $channelArray
     */
    public function __construct(array $channelArray)
    {
        $this->channelIDs = $channelArray;
        $this->loadChannels();
    }

    private function loadChannels()
    {
        foreach ($this->channelIDs as $channelID) {
            $this->channels[] = new channel($channelID);
        }
    }

    public function render()
    {
        foreach ($this->channels as $channelObj) {

            $channel = new SimpleXMLElement($channelObj->data);

            foreach ($channel->entry as $video) {

                $media = $video->children('http://search.yahoo.com/mrss/')->group;
                $YTID = (string)$video->children('http://www.youtube.com/xml/schemas/2015')->videoId;

                $timestamp = DateTime::createFromFormat('Y-m-d\TH:i:sP', (string)$video->published)->getTimestamp();

                $this->videos[(int)$timestamp] = (object)[
                    'ID' => $YTID,
                    'title' => $video->title,
                    'date' => (string)$video->published,
                    'timestamp' => $timestamp,
                    'URL' => 'https://www.youtube.com/embed/'.$YTID,
                    'thumbnail' => 'https://i4.ytimg.com/vi/' . $YTID . '/maxresdefault.jpg',
                ];
            }
        }

        krsort($this->videos, SORT_NUMERIC);

        $this->videos = array_slice($this->videos, 0, 60);

    }

}