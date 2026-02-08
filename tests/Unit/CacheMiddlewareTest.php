<?php

declare(strict_types=1);

use SerhiiLabs\AiValidator\Cache\CacheKey;
use SerhiiLabs\AiValidator\Cache\CacheMiddleware;
use SerhiiLabs\AiValidator\Config\CacheConfig;
use SerhiiLabs\AiValidator\Contracts\ResultCacheInterface;
use SerhiiLabs\AiValidator\ValueObjects\ValidationContext;
use SerhiiLabs\AiValidator\ValueObjects\ValidationResult;

it('returns cached result on cache hit', function () {
    $cache = new class implements ResultCacheInterface
    {
        public function get(CacheKey $key): ?ValidationResult
        {
            return ValidationResult::passed();
        }

        public function put(CacheKey $key, ValidationResult $result, int $ttl): void {}
    };

    $middleware = new CacheMiddleware($cache, new CacheConfig(true, 3600, null, 'ai_validator'));
    $ctx = new ValidationContext('test', 'rule', 'field');

    $called = false;
    $result = $middleware->handle($ctx, function () use (&$called): ValidationResult {
        $called = true;

        return ValidationResult::failed('should not reach');
    });

    expect($result->passed)->toBeTrue()
        ->and($called)->toBeFalse();
});

it('calls next and caches result on cache miss', function () {
    $cache = new class implements ResultCacheInterface
    {
        public array $stored = [];

        public function get(CacheKey $key): ?ValidationResult
        {
            return null;
        }

        public function put(CacheKey $key, ValidationResult $result, int $ttl): void
        {
            $this->stored[] = ['key' => $key->hash, 'result' => $result, 'ttl' => $ttl];
        }
    };

    $middleware = new CacheMiddleware($cache, new CacheConfig(true, 3600, null, 'ai_validator'));
    $ctx = new ValidationContext('test', 'rule', 'field');

    $result = $middleware->handle($ctx, fn (): ValidationResult => ValidationResult::passed());

    expect($result->passed)->toBeTrue()
        ->and($cache->stored)->toHaveCount(1)
        ->and($cache->stored[0]['result']->passed)->toBeTrue();
});

it('bypasses cache when caching disabled on context', function () {
    $cache = new class implements ResultCacheInterface
    {
        public int $getCount = 0;

        public function get(CacheKey $key): ?ValidationResult
        {
            $this->getCount++;

            return null;
        }

        public function put(CacheKey $key, ValidationResult $result, int $ttl): void {}
    };

    $middleware = new CacheMiddleware($cache, new CacheConfig(true, 3600, null, 'ai_validator'));
    $ctx = new ValidationContext('test', 'rule', 'field');
    $ctx->cachingEnabled = false;

    $middleware->handle($ctx, fn (): ValidationResult => ValidationResult::passed());

    expect($cache->getCount)->toBe(0);
});

it('bypasses cache when caching disabled in config', function () {
    $cache = new class implements ResultCacheInterface
    {
        public int $getCount = 0;

        public function get(CacheKey $key): ?ValidationResult
        {
            $this->getCount++;

            return null;
        }

        public function put(CacheKey $key, ValidationResult $result, int $ttl): void {}
    };

    $middleware = new CacheMiddleware($cache, new CacheConfig(false, 3600, null, 'ai_validator'));
    $ctx = new ValidationContext('test', 'rule', 'field');

    $middleware->handle($ctx, fn (): ValidationResult => ValidationResult::passed());

    expect($cache->getCount)->toBe(0);
});

it('uses context TTL override when set', function () {
    $cache = new class implements ResultCacheInterface
    {
        public array $stored = [];

        public function get(CacheKey $key): ?ValidationResult
        {
            return null;
        }

        public function put(CacheKey $key, ValidationResult $result, int $ttl): void
        {
            $this->stored[] = ['ttl' => $ttl];
        }
    };

    $middleware = new CacheMiddleware($cache, new CacheConfig(true, 3600, null, 'ai_validator'));
    $ctx = new ValidationContext('test', 'rule', 'field');
    $ctx->cacheTtl = 7200;

    $middleware->handle($ctx, fn (): ValidationResult => ValidationResult::passed());

    expect($cache->stored[0]['ttl'])->toBe(7200);
});

it('uses config TTL when context has no override', function () {
    $cache = new class implements ResultCacheInterface
    {
        public array $stored = [];

        public function get(CacheKey $key): ?ValidationResult
        {
            return null;
        }

        public function put(CacheKey $key, ValidationResult $result, int $ttl): void
        {
            $this->stored[] = ['ttl' => $ttl];
        }
    };

    $middleware = new CacheMiddleware($cache, new CacheConfig(true, 3600, null, 'ai_validator'));
    $ctx = new ValidationContext('test', 'rule', 'field');

    $middleware->handle($ctx, fn (): ValidationResult => ValidationResult::passed());

    expect($cache->stored[0]['ttl'])->toBe(3600);
});

it('generates deterministic cache key for same input', function () {
    $cache = new class implements ResultCacheInterface
    {
        public array $stored = [];

        public function get(CacheKey $key): ?ValidationResult
        {
            return null;
        }

        public function put(CacheKey $key, ValidationResult $result, int $ttl): void
        {
            $this->stored[] = $key->hash;
        }
    };

    $middleware = new CacheMiddleware($cache, new CacheConfig(true, 3600, null, 'ai_validator'));

    $ctx1 = new ValidationContext('value', 'rule', 'field');
    $middleware->handle($ctx1, fn (): ValidationResult => ValidationResult::passed());

    $ctx2 = new ValidationContext('value', 'rule', 'field');
    $middleware->handle($ctx2, fn (): ValidationResult => ValidationResult::passed());

    expect($cache->stored[0])->toBe($cache->stored[1]);
});

it('generates different cache key for different provider', function () {
    $cache = new class implements ResultCacheInterface
    {
        public array $stored = [];

        public function get(CacheKey $key): ?ValidationResult
        {
            return null;
        }

        public function put(CacheKey $key, ValidationResult $result, int $ttl): void
        {
            $this->stored[] = $key->hash;
        }
    };

    $middleware = new CacheMiddleware($cache, new CacheConfig(true, 3600, null, 'ai_validator'));

    $ctx1 = new ValidationContext('value', 'rule', 'field');
    $ctx1->provider = 'openai';
    $ctx1->model = 'gpt-4';
    $middleware->handle($ctx1, fn (): ValidationResult => ValidationResult::passed());

    $ctx2 = new ValidationContext('value', 'rule', 'field');
    $ctx2->provider = 'anthropic';
    $ctx2->model = 'gpt-4';
    $middleware->handle($ctx2, fn (): ValidationResult => ValidationResult::passed());

    expect($cache->stored[0])->not->toBe($cache->stored[1]);
});

it('generates different cache key for different model', function () {
    $cache = new class implements ResultCacheInterface
    {
        public array $stored = [];

        public function get(CacheKey $key): ?ValidationResult
        {
            return null;
        }

        public function put(CacheKey $key, ValidationResult $result, int $ttl): void
        {
            $this->stored[] = $key->hash;
        }
    };

    $middleware = new CacheMiddleware($cache, new CacheConfig(true, 3600, null, 'ai_validator'));

    $ctx1 = new ValidationContext('value', 'rule', 'field');
    $ctx1->model = 'gpt-4';
    $middleware->handle($ctx1, fn (): ValidationResult => ValidationResult::passed());

    $ctx2 = new ValidationContext('value', 'rule', 'field');
    $ctx2->model = 'claude-3';
    $middleware->handle($ctx2, fn (): ValidationResult => ValidationResult::passed());

    expect($cache->stored[0])->not->toBe($cache->stored[1]);
});

it('generates same cache key regardless of timeout', function () {
    $cache = new class implements ResultCacheInterface
    {
        public array $stored = [];

        public function get(CacheKey $key): ?ValidationResult
        {
            return null;
        }

        public function put(CacheKey $key, ValidationResult $result, int $ttl): void
        {
            $this->stored[] = $key->hash;
        }
    };

    $middleware = new CacheMiddleware($cache, new CacheConfig(true, 3600, null, 'ai_validator'));

    $ctx1 = new ValidationContext('value', 'rule', 'field');
    $ctx1->timeout = 10;
    $middleware->handle($ctx1, fn (): ValidationResult => ValidationResult::passed());

    $ctx2 = new ValidationContext('value', 'rule', 'field');
    $ctx2->timeout = 30;
    $middleware->handle($ctx2, fn (): ValidationResult => ValidationResult::passed());

    expect($cache->stored[0])->toBe($cache->stored[1]);
});

it('generates different cache key for different input', function () {
    $cache = new class implements ResultCacheInterface
    {
        public array $stored = [];

        public function get(CacheKey $key): ?ValidationResult
        {
            return null;
        }

        public function put(CacheKey $key, ValidationResult $result, int $ttl): void
        {
            $this->stored[] = $key->hash;
        }
    };

    $middleware = new CacheMiddleware($cache, new CacheConfig(true, 3600, null, 'ai_validator'));

    $ctx1 = new ValidationContext('value1', 'rule', 'field');
    $middleware->handle($ctx1, fn (): ValidationResult => ValidationResult::passed());

    $ctx2 = new ValidationContext('value2', 'rule', 'field');
    $middleware->handle($ctx2, fn (): ValidationResult => ValidationResult::passed());

    expect($cache->stored[0])->not->toBe($cache->stored[1]);
});
