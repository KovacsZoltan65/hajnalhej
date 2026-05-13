<?php

declare(strict_types=1);

namespace App\Services\Cache;

use Illuminate\Support\Facades\Cache;

class CacheVersionService
{
    public function get(string $namespace): int
    {
        return (int) Cache::rememberForever($this->key($namespace), fn (): int => 1);
    }

    public function bump(string $namespace): int
    {
        $key = $this->key($namespace);

        Cache::add($key, 1, null);

        $version = Cache::increment($key);

        if (\is_int($version)) {
            return $version;
        }

        $version = $this->get($namespace) + 1;
        Cache::forever($key, $version);

        return $version;
    }

    public function reset(string $namespace): void
    {
        Cache::forget($this->key($namespace));
    }

    private function key(string $namespace): string
    {
        return "cache-version:{$namespace}";
    }
}
