<?php

declare(strict_types=1);

namespace SerhiiLabs\AiValidator\ValueObjects;

final readonly class ValidationResult
{
    public function __construct(
        public bool $passed,
        public string $explanation,
    ) {}

    public static function passed(): self
    {
        return new self(passed: true, explanation: '');
    }

    public static function failed(string $explanation): self
    {
        return new self(passed: false, explanation: $explanation);
    }
}
