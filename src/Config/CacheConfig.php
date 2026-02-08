<?php

declare(strict_types=1);

namespace SerhiiLabs\AiValidator\Config;

final readonly class CacheConfig
{
    public function __construct(
        public bool $enabled,
        public int $ttl,
        public ?string $store,
        public string $prefix,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            enabled: config()->boolean('ai-validator.cache.enabled'),
            ttl: config()->integer('ai-validator.cache.ttl'),
            store: is_string($store = config('ai-validator.cache.store')) ? $store : null,
            prefix: config()->string('ai-validator.cache.prefix'),
        );
    }
}
