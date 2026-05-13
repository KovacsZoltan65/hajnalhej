<?php

declare(strict_types=1);

namespace App\Services\Cache;

use JsonException;

final class CacheKeyService
{
    /**
     * @param  array<string|int, mixed>  $payload
     *
     * @throws JsonException
     */
    public static function stableHash(array $payload): string
    {
        $normalized = self::normalize($payload);
        $json = json_encode($normalized, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return substr(hash('sha256', $json), 0, 12);
    }

    /**
     * @param  array<string|int, mixed>  $payload
     *
     * @throws JsonException
     */
    public static function make(string $namespace, int $version, array $payload = []): string
    {
        return sprintf('%s:v%d:%s', $namespace, $version, self::stableHash($payload));
    }

    private static function normalize(mixed $value): mixed
    {
        if (! \is_array($value)) {
            return $value;
        }

        if (! array_is_list($value)) {
            ksort($value);
        }

        foreach ($value as $key => $item) {
            $value[$key] = self::normalize($item);
        }

        return $value;
    }
}
