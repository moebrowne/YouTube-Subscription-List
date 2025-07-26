<?php

declare(strict_types=1);

class ChannelCollection implements \Countable, \IteratorAggregate, \ArrayAccess
{
    /** @var array<string, Channel> */
    private array $channels = [];

    private string $channelsPath;

    public function __construct(string $channelsPath)
    {
        $this->channelsPath = $channelsPath;
        $this->loadChannels();
    }

    private function loadChannels(): void
    {
        if (file_exists($this->channelsPath) === false) {
            return;
        }

        $channelUrls = file($this->channelsPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($channelUrls as $channelUrl) {
            if (str_starts_with($channelUrl, '#')) {
                continue;
            }

            $this->channels[] = new Channel(
                url: str_replace('*', '', $channelUrl),
                featured: str_starts_with($channelUrl, '*'),
            );
        }

        $this->channels = pipe(
            $channelUrls,
            filterOutComments: fn (string $feedUrl): bool => str_starts_with($feedUrl, '#') === false,
            mapToDto: function (string $feedUrl): Channel {
                return new Channel(
                    url: str_replace('*', '', $feedUrl),
                    featured: str_starts_with($feedUrl, '*'),
                );
            }
        );
    }

    public function save(): void
    {
        file_put_contents(
            $this->channelsPath,
            json_encode($this->channels, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT),
        );
    }

    /**
     * Returns the channels as an array to use with array_* functions
     *
     * @return array<string, Channel>
     */
    public function toArray(): array
    {
        return $this->channels;
    }

    public function count(): int
    {
        return count($this->channels);
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->channels);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->channels[$offset]);
    }

    public function offsetGet(mixed $offset): ?Channel
    {
        return $this->channels[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!$value instanceof Channel) {
            throw new \TypeError('Value must be an instance of ' . Channel::class);
        }

        $this->channels[$value->id] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->channels[$offset]);
    }
}
