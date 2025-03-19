<?php

declare(strict_types=1);

class Video
{
    public string $embedUrl {
        get => 'https://www.youtube.com/embed/' . $this->id . '?autoplay=1';
    }

    public function __construct(
        public readonly string $id,
        public readonly string $channelId,
        public readonly string $title,
        public readonly DateTimeImmutable $publishedAt,
        public readonly string $description
    ) {
    }
}