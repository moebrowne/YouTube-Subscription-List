<?php

declare(strict_types=1);

class Video implements JsonSerializable
{
    public string $embedUrl {
        get => 'https://www.youtube.com/embed/' . $this->id . '?autoplay=1';
    }

    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly DateTimeImmutable $publishedAt,
        public readonly string $description,
        public readonly bool $featured = false,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['title'],
            new DateTimeImmutable($data['publishedAt']),
            $data['description'],
            $data['featured'] ?? false,
        );
    }

    public function jsonSerialize(): array
    {
        return [
            ...(array)$this,
            'publishedAt' => $this->publishedAt->format('c'),
        ];
    }
}