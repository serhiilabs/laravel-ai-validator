<?php

declare(strict_types=1);

use SerhiiLabs\AiValidator\Options\WithoutRateLimit;
use SerhiiLabs\AiValidator\ValueObjects\ValidationContext;
use SerhiiLabs\AiValidator\ValueObjects\ValidationResult;

it('disables rate limiting on context', function () {
    $option = new WithoutRateLimit;
    $ctx = new ValidationContext('value', 'rule', 'field');

    expect($ctx->rateLimitEnabled)->toBeTrue();

    $option->handle($ctx, fn (ValidationContext $c): ValidationResult => ValidationResult::passed());

    expect($ctx->rateLimitEnabled)->toBeFalse();
});
