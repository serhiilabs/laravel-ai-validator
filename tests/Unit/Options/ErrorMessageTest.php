<?php

declare(strict_types=1);

use SerhiiLabs\AiValidator\Options\ErrorMessage;
use SerhiiLabs\AiValidator\ValueObjects\ValidationContext;
use SerhiiLabs\AiValidator\ValueObjects\ValidationResult;

it('replaces explanation when validation fails', function () {
    $option = new ErrorMessage('Custom error.');
    $ctx = new ValidationContext('value', 'rule', 'field');

    $result = $option->handle($ctx, fn (ValidationContext $c): ValidationResult => ValidationResult::failed('Original error.'));

    expect($result->passed)->toBeFalse()
        ->and($result->explanation)->toBe('Custom error.');
});

it('does not modify result when validation passes', function () {
    $option = new ErrorMessage('Custom error.');
    $ctx = new ValidationContext('value', 'rule', 'field');

    $result = $option->handle($ctx, fn (ValidationContext $c): ValidationResult => ValidationResult::passed());

    expect($result->passed)->toBeTrue()
        ->and($result->explanation)->toBe('');
});

it('throws exception for empty error message', function () {
    new ErrorMessage('');
})->throws(InvalidArgumentException::class, 'Error message cannot be empty.');

it('throws exception for whitespace-only error message', function () {
    new ErrorMessage('   ');
})->throws(InvalidArgumentException::class, 'Error message cannot be empty.');
