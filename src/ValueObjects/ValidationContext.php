<?php

declare(strict_types=1);

namespace SerhiiLabs\AiValidator\ValueObjects;

final class ValidationContext
{
    public ?string $provider = null;

    public ?string $model = null;

    public ?int $timeout = null;

    public bool $cachingEnabled = true;

    public ?int $cacheTtl = null;

    public bool $rateLimitEnabled = true;

    public function __construct(
        public readonly string $value,
        public readonly string $description,
        public readonly string $attribute,
    ) {}
}
