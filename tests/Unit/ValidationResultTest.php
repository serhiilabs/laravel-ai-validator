<?php

declare(strict_types=1);

use SerhiiLabs\AiValidator\ValueObjects\ValidationResult;

it('creates a passed result via factory', function () {
    $result = ValidationResult::passed();

    expect($result->passed)->toBeTrue()
        ->and($result->explanation)->toBe('');
});

it('creates a failed result via factory', function () {
    $result = ValidationResult::failed('Not a real company.');

    expect($result->passed)->toBeFalse()
        ->and($result->explanation)->toBe('Not a real company.');
});
