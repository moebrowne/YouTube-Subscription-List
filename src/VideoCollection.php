<?php

declare(strict_types=1);

class VideoCollection implements \Countable, \IteratorAggregate, \ArrayAccess
{
    /** @var array<string, Video> */
    private array $videos = [];

    private string $videosPath;

    public function __construct(string $videosPath)
    {
        $this->videosPath = $videosPath;
        $this->loadVideos();
    }

    private function loadVideos(): void
    {
        if (file_exists($this->videosPath) === false) {
            file_put_contents($this->videosPath, '{}');
        }

        $videosData = json_decode(
            file_get_contents($this->videosPath),
            associative: true,
            flags: JSON_THROW_ON_ERROR
        );

        foreach ($videosData as $videoData) {
            $video = Video::fromArray($videoData);
            $this->videos[$video->id] = $video;
        }

        $this->sort();
    }

    public function sort(): void
    {
        uasort($this->videos, static fn(Video $a, Video $b): int => $b->publishedAt <=> $a->publishedAt);
    }

    public function save(): void
    {
        file_put_contents(
            $this->videosPath,
            json_encode($this->videos, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT),
        );
    }

    public function add(Video ...$videos): void
    {
        foreach ($videos as $video) {
            $video->watched = $this->videos[$video->id]->watched ?? false;
            $this->videos[$video->id] = $video;
        }

        $this->sort();

        $this->save();
    }

    /**
     * Returns the videos as an array to use with array_* functions
     *
     * @return array<string, Video>
     */
    public function toArray(): array
    {
        return $this->videos;
    }

    public function count(): int
    {
        return count($this->videos);
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->videos);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->videos[$offset]);
    }

    public function offsetGet(mixed $offset): ?Video
    {
        return $this->videos[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!$value instanceof Video) {
            throw new \TypeError('Value must be an instance of ' . Video::class);
        }

        $this->videos[$value->id] = $value;
        $this->sort();
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->videos[$offset]);
    }

    public function limit(int $int): self
    {
        $this->videos = array_splice($this->videos, 0, $int);

        return $this;
    }
}
