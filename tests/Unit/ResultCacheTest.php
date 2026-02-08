<?php

declare(strict_types=1);

use SerhiiLabs\AiValidator\Cache\CacheKey;
use SerhiiLabs\AiValidator\Cache\ResultCache;
use SerhiiLabs\AiValidator\Config\CacheConfig;
use SerhiiLabs\AiValidator\ValueObjects\ValidationResult;

beforeEach(function () {
    $this->cache = new ResultCache(new CacheConfig(
        enabled: true,
        ttl: 3600,
        store: null,
        prefix: 'ai_validator',
    ));
});

it('returns null for uncached entry', function () {
    $result = $this->cache->get(new CacheKey('abc123'));

    expect($result)->toBeNull();
});

it('stores and retrieves cached result', function () {
    $key = new CacheKey('test_hash_1');
    $original = new ValidationResult(passed: true, explanation: 'Looks good.');

    $this->cache->put($key, $original, 3600);

    $cached = $this->cache->get($key);

    expect($cached)->not->toBeNull()
        ->and($cached->passed)->toBeTrue()
        ->and($cached->explanation)->toBe('Looks good.');
});

it('returns different results for different keys', function () {
    $this->cache->put(new CacheKey('hash_a'), ValidationResult::passed(), 3600);

    $cached = $this->cache->get(new CacheKey('hash_b'));

    expect($cached)->toBeNull();
});

it('returns same result for identical keys', function () {
    $key = new CacheKey('same_hash');

    $this->cache->put($key, ValidationResult::passed(), 3600);

    $cached = $this->cache->get($key);

    expect($cached)->not->toBeNull()
        ->and($cached->passed)->toBeTrue();
});
