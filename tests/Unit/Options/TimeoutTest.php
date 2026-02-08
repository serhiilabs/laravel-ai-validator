<?php

declare(strict_types=1);

use SerhiiLabs\AiValidator\Options\Timeout;
use SerhiiLabs\AiValidator\ValueObjects\ValidationContext;
use SerhiiLabs\AiValidator\ValueObjects\ValidationResult;

it('sets timeout on context', function () {
    $option = new Timeout(30);
    $ctx = new ValidationContext('value', 'rule', 'field');

    $option->handle($ctx, fn (ValidationContext $c): ValidationResult => ValidationResult::passed());

    expect($ctx->timeout)->toBe(30);
});

it('passes through to next middleware', function () {
    $option = new Timeout(10);
    $ctx = new ValidationContext('value', 'rule', 'field');

    $result = $option->handle($ctx, fn (ValidationContext $c): ValidationResult => ValidationResult::failed('next'));

    expect($result->passed)->toBeFalse();
});
