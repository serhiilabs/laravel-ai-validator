<?php

declare(strict_types=1);

use SerhiiLabs\AiValidator\Config\RateLimitConfig;

it('creates from constructor', function () {
    $config = new RateLimitConfig(
        enabled: true,
        maxAttempts: 100,
        decaySeconds: 120,
    );

    expect($config->enabled)->toBeTrue()
        ->and($config->maxAttempts)->toBe(100)
        ->and($config->decaySeconds)->toBe(120);
});

it('creates from Laravel config', function () {
    config()->set('ai-validator.rate_limit.enabled', false);
    config()->set('ai-validator.rate_limit.max_attempts', 30);
    config()->set('ai-validator.rate_limit.decay_seconds', 300);

    $config = RateLimitConfig::fromConfig();

    expect($config->enabled)->toBeFalse()
        ->and($config->maxAttempts)->toBe(30)
        ->and($config->decaySeconds)->toBe(300);
});
