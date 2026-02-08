<?php

declare(strict_types=1);

namespace SerhiiLabs\AiValidator\Cache;

use Illuminate\Support\Facades\Cache;
use SerhiiLabs\AiValidator\Config\CacheConfig;
use SerhiiLabs\AiValidator\Contracts\ResultCacheInterface;
use SerhiiLabs\AiValidator\ValueObjects\ValidationResult;

final class ResultCache implements ResultCacheInterface
{
    public function __construct(
        private CacheConfig $config,
    ) {}

    public function get(CacheKey $key): ?ValidationResult
    {
        /** @var array{passed: bool, explanation: string}|null $cached */
        $cached = Cache::store($this->config->store)->get($this->buildKey($key));

        if ($cached === null) {
            return null;
        }

        return new ValidationResult(
            passed: $cached['passed'],
            explanation: $cached['explanation'],
        );
    }

    public function put(CacheKey $key, ValidationResult $result, int $ttl): void
    {
        Cache::store($this->config->store)->put($this->buildKey($key), [
            'passed' => $result->passed,
            'explanation' => $result->explanation,
        ], $ttl);
    }

    private function buildKey(CacheKey $key): string
    {
        return sprintf('%s:%s', $this->config->prefix, $key->hash);
    }
}
