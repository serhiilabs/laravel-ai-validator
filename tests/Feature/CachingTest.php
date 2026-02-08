<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Validator;
use Prism\Prism\Facades\Prism;
use SerhiiLabs\AiValidator\AiRule;
use SerhiiLabs\AiValidator\Contracts\AiValidatorInterface;

it('caches result and returns it on second call', function () {
    $fake = Prism::fake([
        fakeStructuredResponse(true, 'Valid.'),
    ]);

    $makeValidator = fn () => Validator::make(
        ['company' => 'Acme Corp'],
        ['company' => [new AiRule('real company name')]],
    );

    $makeValidator()->passes();

    $result = $makeValidator()->passes();

    expect($result)->toBeTrue();
    $fake->assertCallCount(1);
});

it('bypasses cache when withoutCache is set', function () {
    $fake = Prism::fake([
        fakeStructuredResponse(true, 'Valid.'),
        fakeStructuredResponse(true, 'Valid again.'),
    ]);

    $makeValidator = fn () => Validator::make(
        ['company' => 'Acme Corp'],
        ['company' => [AiRule::make('real company name')->withoutCache()]],
    );

    $makeValidator()->passes();
    $makeValidator()->passes();

    $fake->assertCallCount(2);
});

it('does not cache when caching is disabled in config', function () {
    config(['ai-validator.cache.enabled' => false]);
    app()->forgetInstance(AiValidatorInterface::class);

    $fake = Prism::fake([
        fakeStructuredResponse(true, 'Valid.'),
        fakeStructuredResponse(true, 'Valid again.'),
    ]);

    $makeValidator = fn () => Validator::make(
        ['company' => 'Acme Corp'],
        ['company' => [new AiRule('real company name')]],
    );

    $makeValidator()->passes();
    $makeValidator()->passes();

    $fake->assertCallCount(2);
});
