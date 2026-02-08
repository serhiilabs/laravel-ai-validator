<?php

declare(strict_types=1);

namespace SerhiiLabs\AiValidator\Contracts;

use SerhiiLabs\AiValidator\Cache\CacheKey;
use SerhiiLabs\AiValidator\ValueObjects\ValidationResult;

interface ResultCacheInterface
{
    public function get(CacheKey $key): ?ValidationResult;

    public function put(CacheKey $key, ValidationResult $result, int $ttl): void;
}
