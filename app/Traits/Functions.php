<?php

declare(strict_types=1);

namespace App\Traits;

trait Functions
{
    /**
     * Egyedi Cache kulcsot generál
     * @param string $tag
     * @param string $key
     * @return string
     */
    public function generateCacheKey(string $tag, string $key): string
    {
        return "{$tag}_" . md5($key);
    }
}