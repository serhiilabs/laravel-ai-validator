<?php

declare(strict_types=1);

namespace SerhiiLabs\AiValidator\Cache;

use SerhiiLabs\AiValidator\ValueObjects\ValidationContext;

final readonly class CacheKey
{
    public function __construct(
        public string $hash,
    ) {}

    public static function fromContext(ValidationContext $ctx): self
    {
        $payload = json_encode([
            'description' => $ctx->description,
            'value' => $ctx->value,
            'provider' => $ctx->provider,
            'model' => $ctx->model,
        ], JSON_THROW_ON_ERROR);

        return new self(hash('xxh128', $payload));
    }
}
