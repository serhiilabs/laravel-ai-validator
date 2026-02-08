<?php

declare(strict_types=1);

namespace SerhiiLabs\AiValidator\ValueObjects;

final readonly class DriverRequest
{
    public function __construct(
        public string $systemPrompt,
        public string $userPrompt,
        public ?string $provider = null,
        public ?string $model = null,
        public ?int $timeout = null,
    ) {}
}
