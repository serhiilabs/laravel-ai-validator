<?php

declare(strict_types=1);

namespace SerhiiLabs\AiValidator\Config;

final readonly class RateLimitConfig
{
    public function __construct(
        public bool $enabled,
        public int $maxAttempts,
        public int $decaySeconds,
    ) {}

    public static function fromConfig(): self
    {
        return new self(
            enabled: config()->boolean('ai-validator.rate_limit.enabled'),
            maxAttempts: config()->integer('ai-validator.rate_limit.max_attempts'),
            decaySeconds: config()->integer('ai-validator.rate_limit.decay_seconds'),
        );
    }
}
