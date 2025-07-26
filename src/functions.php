<?php

declare(strict_types=1);

function pipe(mixed $value, callable ...$operations): mixed
{
    foreach ($operations as $name => $callback) {
        $callback = match (true) {
            is_int($name) => $callback,
            str_starts_with($name, 'filter') => fn() => array_filter($value, $callback),
            str_starts_with($name, 'map') => fn() => array_map($callback, $value),
            str_starts_with($name, 'sortBy') => function () use (&$value, $callback): array {
                usort($value, $callback);
                return $value;
            },
            default => $callback,
        };

        $value = $callback($value);
    }

    return $value;
}
