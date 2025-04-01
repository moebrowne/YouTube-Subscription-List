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
            file_put_contents($this->channelsPath, '{}');
        }

        $channelsData = json_decode(
            file_get_contents($this->channelsPath),
            associative: true,
            flags: JSON_THROW_ON_ERROR
        );

        foreach ($channelsData as $id => $channel) {
            $this->channels[$id] = new Channel(
                id: $id,
                featured: $channel['featured'] ?? false,
            );
        }
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
