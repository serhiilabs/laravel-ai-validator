<?php

declare(strict_types=1);

use SerhiiLabs\AiValidator\Config\CacheConfig;

it('creates from constructor', function () {
    $config = new CacheConfig(
        enabled: false,
        ttl: 7200,
        store: 'redis',
        prefix: 'custom_prefix',
    );

    expect($config->enabled)->toBeFalse()
        ->and($config->ttl)->toBe(7200)
        ->and($config->store)->toBe('redis')
        ->and($config->prefix)->toBe('custom_prefix');
});

it('creates from Laravel config', function () {
    config()->set('ai-validator.cache.enabled', false);
    config()->set('ai-validator.cache.ttl', 1800);
    config()->set('ai-validator.cache.store', 'file');
    config()->set('ai-validator.cache.prefix', 'test_prefix');

    $config = CacheConfig::fromConfig();

    expect($config->enabled)->toBeFalse()
        ->and($config->ttl)->toBe(1800)
        ->and($config->store)->toBe('file')
        ->and($config->prefix)->toBe('test_prefix');
});
