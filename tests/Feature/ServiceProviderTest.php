<?php

declare(strict_types=1);

use SerhiiLabs\AiValidator\AiValidator;
use SerhiiLabs\AiValidator\AiValidatorServiceProvider;
use SerhiiLabs\AiValidator\Cache\ResultCache;
use SerhiiLabs\AiValidator\Contracts\AiValidatorInterface;
use SerhiiLabs\AiValidator\Contracts\DriverInterface;
use SerhiiLabs\AiValidator\Contracts\ResultCacheInterface;
use SerhiiLabs\AiValidator\Drivers\PrismDriver;

it('throws RuntimeException when no driver configured', function () {
    config()->set('ai-validator.driver', null);
    app()->forgetInstance(DriverInterface::class);

    app(DriverInterface::class);
})->throws(RuntimeException::class, 'No AI driver configured');

it('resolves PrismDriver when configured', function () {
    expect(app(DriverInterface::class))->toBeInstanceOf(PrismDriver::class);
});

it('resolves AiValidatorInterface', function () {
    expect(app(AiValidatorInterface::class))->toBeInstanceOf(AiValidator::class);
});

it('resolves ResultCacheInterface', function () {
    expect(app(ResultCacheInterface::class))->toBeInstanceOf(ResultCache::class);
});

it('provides deferred services', function () {
    $provider = new AiValidatorServiceProvider(app());

    expect($provider->provides())->toBe([
        DriverInterface::class,
        AiValidatorInterface::class,
        ResultCacheInterface::class,
    ]);
});

it('does not override existing driver binding', function () {
    $customDriver = new class implements DriverInterface
    {
        public function send(
            \SerhiiLabs\AiValidator\ValueObjects\DriverRequest $request,
        ): \SerhiiLabs\AiValidator\ValueObjects\DriverResponse {
            return new \SerhiiLabs\AiValidator\ValueObjects\DriverResponse(true, 'Custom.');
        }
    };

    app()->instance(DriverInterface::class, $customDriver);

    (new AiValidatorServiceProvider(app()))->register();

    expect(app(DriverInterface::class))->toBe($customDriver);
});
