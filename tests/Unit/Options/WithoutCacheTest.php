<?php

declare(strict_types=1);

use SerhiiLabs\AiValidator\Options\WithoutCache;
use SerhiiLabs\AiValidator\ValueObjects\ValidationContext;
use SerhiiLabs\AiValidator\ValueObjects\ValidationResult;

it('disables caching on context', function () {
    $option = new WithoutCache;
    $ctx = new ValidationContext('value', 'rule', 'field');

    expect($ctx->cachingEnabled)->toBeTrue();

    $option->handle($ctx, fn (ValidationContext $c): ValidationResult => ValidationResult::passed());

    expect($ctx->cachingEnabled)->toBeFalse();
});
