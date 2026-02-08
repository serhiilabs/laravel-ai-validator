<?php

declare(strict_types=1);

use SerhiiLabs\AiValidator\Options\Using;
use SerhiiLabs\AiValidator\ValueObjects\ValidationContext;
use SerhiiLabs\AiValidator\ValueObjects\ValidationResult;

it('sets provider and model on context', function () {
    $option = new Using('anthropic', 'claude-3');
    $ctx = new ValidationContext('value', 'rule', 'field');

    $option->handle($ctx, fn (ValidationContext $c): ValidationResult => ValidationResult::passed());

    expect($ctx->provider)->toBe('anthropic')
        ->and($ctx->model)->toBe('claude-3');
});

it('passes through to next middleware', function () {
    $option = new Using('openai', 'gpt-4');
    $ctx = new ValidationContext('value', 'rule', 'field');

    $result = $option->handle($ctx, fn (ValidationContext $c): ValidationResult => ValidationResult::failed('next'));

    expect($result->passed)->toBeFalse()
        ->and($result->explanation)->toBe('next');
});

it('throws exception for empty provider', function () {
    new Using('', 'gpt-4');
})->throws(InvalidArgumentException::class, 'Provider and model cannot be empty.');

it('throws exception for empty model', function () {
    new Using('openai', '');
})->throws(InvalidArgumentException::class, 'Provider and model cannot be empty.');

it('throws exception for whitespace-only provider', function () {
    new Using('   ', 'gpt-4');
})->throws(InvalidArgumentException::class, 'Provider and model cannot be empty.');
