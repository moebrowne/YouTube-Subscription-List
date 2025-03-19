<?php

declare(strict_types=1);

readonly class Channel
{
    public function __construct(
        public string $id,
        public bool   $featured,
    ) {
    }
}