<?php

declare(strict_types=1);

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Prism\Prism\Facades\Prism;
use SerhiiLabs\AiValidator\AiRule;
use SerhiiLabs\AiValidator\Contracts\AiValidatorInterface;
use SerhiiLabs\AiValidator\Exceptions\RateLimitExceededException;

beforeEach(function () {
    RateLimiter::clear('ai_validator');
});

it('returns rate limit error when limit exceeded', function () {
    config(['ai-validator.rate_limit.max_attempts' => 2]);
    app()->forgetInstance(AiValidatorInterface::class);

    Prism::fake([
        fakeStructuredResponse(true, 'Valid.'),
        fakeStructuredResponse(true, 'Valid.'),
    ]);

    $makeValidator = fn () => Validator::make(
        ['name' => 'Acme Corp'],
        ['name' => [AiRule::make('real company name')->withoutCache()]],
    );

    expect($makeValidator()->passes())->toBeTrue();
    expect($makeValidator()->passes())->toBeTrue();

    $result = $makeValidator();
    expect($result->passes())->toBeFalse();
    expect($result->errors()->first('name'))->toContain('Too many AI validation requests');
});

it('allows requests under the limit', function () {
    config(['ai-validator.rate_limit.max_attempts' => 10]);
    app()->forgetInstance(AiValidatorInterface::class);

    Prism::fake([
        fakeStructuredResponse(true, 'Valid.'),
        fakeStructuredResponse(true, 'Valid.'),
    ]);

    $makeValidator = fn () => Validator::make(
        ['name' => 'Acme Corp'],
        ['name' => [AiRule::make('real company name')->withoutCache()]],
    );

    expect($makeValidator()->passes())->toBeTrue();
    expect($makeValidator()->passes())->toBeTrue();
});

it('bypasses rate limit when withoutRateLimit is set', function () {
    config(['ai-validator.rate_limit.max_attempts' => 1]);
    app()->forgetInstance(AiValidatorInterface::class);

    Prism::fake([
        fakeStructuredResponse(true, 'Valid.'),
        fakeStructuredResponse(true, 'Valid.'),
        fakeStructuredResponse(true, 'Valid.'),
    ]);

    $makeValidator = fn () => Validator::make(
        ['name' => 'Acme Corp'],
        ['name' => [AiRule::make('real company name')->withoutCache()->withoutRateLimit()]],
    );

    expect($makeValidator()->passes())->toBeTrue();
    expect($makeValidator()->passes())->toBeTrue();
    expect($makeValidator()->passes())->toBeTrue();
});

it('does not count cached responses against rate limit', function () {
    config(['ai-validator.rate_limit.max_attempts' => 1]);
    app()->forgetInstance(AiValidatorInterface::class);

    $fake = Prism::fake([
        fakeStructuredResponse(true, 'Valid.'),
    ]);

    $makeValidator = fn () => Validator::make(
        ['name' => 'Acme Corp'],
        ['name' => [new AiRule('real company name')]],
    );

    expect($makeValidator()->passes())->toBeTrue();
    expect($makeValidator()->passes())->toBeTrue();
    expect($makeValidator()->passes())->toBeTrue();

    $fake->assertCallCount(1);
});

it('does not rate limit when disabled in config', function () {
    config(['ai-validator.rate_limit.enabled' => false]);
    app()->forgetInstance(AiValidatorInterface::class);

    Prism::fake([
        fakeStructuredResponse(true, 'Valid.'),
        fakeStructuredResponse(true, 'Valid.'),
        fakeStructuredResponse(true, 'Valid.'),
    ]);

    $makeValidator = fn () => Validator::make(
        ['name' => 'Acme Corp'],
        ['name' => [AiRule::make('real company name')->withoutCache()]],
    );

    expect($makeValidator()->passes())->toBeTrue();
    expect($makeValidator()->passes())->toBeTrue();
    expect($makeValidator()->passes())->toBeTrue();
});

it('throws RateLimitExceededException from AiValidator', function () {
    config(['ai-validator.rate_limit.max_attempts' => 1]);
    app()->forgetInstance(AiValidatorInterface::class);

    Prism::fake([]);

    RateLimiter::hit('ai_validator', 60);

    $ctx = new \SerhiiLabs\AiValidator\ValueObjects\ValidationContext(
        value: 'test',
        description: 'test',
        attribute: 'field',
    );

    app(AiValidatorInterface::class)->validate($ctx);
})->throws(RateLimitExceededException::class);
