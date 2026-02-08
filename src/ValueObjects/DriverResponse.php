<?php

declare(strict_types=1);

namespace SerhiiLabs\AiValidator\ValueObjects;

final readonly class DriverResponse
{
    public function __construct(
        public bool $passed,
        public string $explanation,
    ) {}
}
