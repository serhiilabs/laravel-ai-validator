<?php

declare(strict_types=1);

use SerhiiLabs\AiValidator\ValueObjects\ValidationContext;

it('stores readonly properties from constructor', function () {
    $ctx = new ValidationContext('my value', 'my rule', 'my_field');

    expect($ctx->value)->toBe('my value')
        ->and($ctx->description)->toBe('my rule')
        ->and($ctx->attribute)->toBe('my_field');
});

it('has correct default values for mutable properties', function () {
    $ctx = new ValidationContext('value', 'rule', 'field');

    expect($ctx->provider)->toBeNull()
        ->and($ctx->model)->toBeNull()
        ->and($ctx->timeout)->toBeNull()
        ->and($ctx->cachingEnabled)->toBeTrue()
        ->and($ctx->cacheTtl)->toBeNull()
        ->and($ctx->rateLimitEnabled)->toBeTrue();
});

it('allows setting mutable properties', function () {
    $ctx = new ValidationContext('value', 'rule', 'field');

    $ctx->provider = 'anthropic';
    $ctx->model = 'claude-3';
    $ctx->timeout = 30;
    $ctx->cachingEnabled = false;
    $ctx->cacheTtl = 7200;
    $ctx->rateLimitEnabled = false;

    expect($ctx->provider)->toBe('anthropic')
        ->and($ctx->model)->toBe('claude-3')
        ->and($ctx->timeout)->toBe(30)
        ->and($ctx->cachingEnabled)->toBeFalse()
        ->and($ctx->cacheTtl)->toBe(7200)
        ->and($ctx->rateLimitEnabled)->toBeFalse();
});
