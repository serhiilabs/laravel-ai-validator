<?php

declare(strict_types=1);

use Illuminate\Support\Facades\RateLimiter;
use SerhiiLabs\AiValidator\Config\RateLimitConfig;
use SerhiiLabs\AiValidator\Exceptions\RateLimitExceededException;
use SerhiiLabs\AiValidator\RateLimit\RateLimitMiddleware;
use SerhiiLabs\AiValidator\ValueObjects\ValidationContext;
use SerhiiLabs\AiValidator\ValueObjects\ValidationResult;

beforeEach(function () {
    RateLimiter::clear('ai_validator');
});

it('allows request when under limit', function () {
    $middleware = new RateLimitMiddleware(new RateLimitConfig(true, 10, 60));
    $ctx = new ValidationContext('value', 'rule', 'field');

    $result = $middleware->handle($ctx, fn (): ValidationResult => ValidationResult::passed());

    expect($result->passed)->toBeTrue();
});

it('throws exception when rate limit exceeded', function () {
    $middleware = new RateLimitMiddleware(new RateLimitConfig(true, 1, 60));
    $ctx = new ValidationContext('value', 'rule', 'field');

    $middleware->handle($ctx, fn (): ValidationResult => ValidationResult::passed());

    $middleware->handle($ctx, fn (): ValidationResult => ValidationResult::passed());
})->throws(RateLimitExceededException::class);

it('bypasses rate limit when disabled in config', function () {
    $middleware = new RateLimitMiddleware(new RateLimitConfig(false, 1, 60));
    $ctx = new ValidationContext('value', 'rule', 'field');

    $middleware->handle($ctx, fn (): ValidationResult => ValidationResult::passed());
    $result = $middleware->handle($ctx, fn (): ValidationResult => ValidationResult::passed());

    expect($result->passed)->toBeTrue();
});

it('bypasses rate limit when disabled on context', function () {
    $middleware = new RateLimitMiddleware(new RateLimitConfig(true, 1, 60));
    $ctx = new ValidationContext('value', 'rule', 'field');
    $ctx->rateLimitEnabled = false;

    $middleware->handle($ctx, fn (): ValidationResult => ValidationResult::passed());
    $result = $middleware->handle($ctx, fn (): ValidationResult => ValidationResult::passed());

    expect($result->passed)->toBeTrue();
});

it('includes retry time in exception message', function () {
    $middleware = new RateLimitMiddleware(new RateLimitConfig(true, 1, 60));
    $ctx = new ValidationContext('value', 'rule', 'field');

    $middleware->handle($ctx, fn (): ValidationResult => ValidationResult::passed());

    try {
        $middleware->handle($ctx, fn (): ValidationResult => ValidationResult::passed());
    } catch (RateLimitExceededException $e) {
        expect($e->getMessage())->toContain('Too many AI validation requests')
            ->and($e->getMessage())->toContain('seconds');

        return;
    }

    $this->fail('Expected RateLimitExceededException was not thrown.');
});
