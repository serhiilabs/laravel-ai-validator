<?php

declare(strict_types=1);

namespace SerhiiLabs\AiValidator\Cache;

use Closure;
use SerhiiLabs\AiValidator\Config\CacheConfig;
use SerhiiLabs\AiValidator\Contracts\ResultCacheInterface;
use SerhiiLabs\AiValidator\Contracts\RuleOptionInterface;
use SerhiiLabs\AiValidator\ValueObjects\ValidationContext;
use SerhiiLabs\AiValidator\ValueObjects\ValidationResult;

final class CacheMiddleware implements RuleOptionInterface
{
    public function __construct(
        private ResultCacheInterface $cache,
        private CacheConfig $config,
    ) {}

    public function handle(ValidationContext $ctx, Closure $next): ValidationResult
    {
        $enabled = $ctx->cachingEnabled && $this->config->enabled;

        if (! $enabled) {
            return $next($ctx);
        }

        $ttl = $ctx->cacheTtl ?? $this->config->ttl;
        $key = CacheKey::fromContext($ctx);

        $cached = $this->cache->get($key);
        if ($cached !== null) {
            return $cached;
        }

        $result = $next($ctx);
        $this->cache->put($key, $result, $ttl);

        return $result;
    }
}
