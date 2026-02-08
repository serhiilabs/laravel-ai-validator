<?php

declare(strict_types=1);

use SerhiiLabs\AiValidator\Options\CacheTtl;
use SerhiiLabs\AiValidator\ValueObjects\ValidationContext;
use SerhiiLabs\AiValidator\ValueObjects\ValidationResult;

it('sets cache TTL on context', function () {
    $option = new CacheTtl(7200);
    $ctx = new ValidationContext('value', 'rule', 'field');

    $option->handle($ctx, fn (ValidationContext $c): ValidationResult => ValidationResult::passed());

    expect($ctx->cacheTtl)->toBe(7200);
});

it('passes through to next middleware', function () {
    $option = new CacheTtl(600);
    $ctx = new ValidationContext('value', 'rule', 'field');

    $result = $option->handle($ctx, fn (ValidationContext $c): ValidationResult => ValidationResult::failed('next'));

    expect($result->passed)->toBeFalse();
});
