<?php

declare(strict_types=1);

use SerhiiLabs\AiValidator\AiValidator;
use SerhiiLabs\AiValidator\Cache\CacheKey;
use SerhiiLabs\AiValidator\Cache\CacheMiddleware;
use SerhiiLabs\AiValidator\Config\CacheConfig;
use SerhiiLabs\AiValidator\Config\RateLimitConfig;
use SerhiiLabs\AiValidator\Contracts\DriverInterface;
use SerhiiLabs\AiValidator\Contracts\ResultCacheInterface;
use SerhiiLabs\AiValidator\Exceptions\DriverException;
use SerhiiLabs\AiValidator\Options\Timeout;
use SerhiiLabs\AiValidator\Options\Using;
use SerhiiLabs\AiValidator\Prompt\PromptBuilder;
use SerhiiLabs\AiValidator\RateLimit\RateLimitMiddleware;
use SerhiiLabs\AiValidator\ValueObjects\DriverRequest;
use SerhiiLabs\AiValidator\ValueObjects\DriverResponse;
use SerhiiLabs\AiValidator\ValueObjects\ValidationContext;
use SerhiiLabs\AiValidator\ValueObjects\ValidationResult;

it('returns passed result from driver', function () {
    $driver = new class implements DriverInterface
    {
        public function send(DriverRequest $request): DriverResponse
        {
            return new DriverResponse(true, 'Valid.');
        }
    };

    $validator = buildAiValidator($driver);
    $ctx = new ValidationContext('Acme Corp', 'real company name', 'company');

    $result = $validator->validate($ctx);

    expect($result->passed)->toBeTrue()
        ->and($result->explanation)->toBe('Valid.');
});

it('returns failed result from driver', function () {
    $driver = new class implements DriverInterface
    {
        public function send(DriverRequest $request): DriverResponse
        {
            return new DriverResponse(false, 'Not a company.');
        }
    };

    $validator = buildAiValidator($driver);
    $ctx = new ValidationContext('asdf', 'real company name', 'company');

    $result = $validator->validate($ctx);

    expect($result->passed)->toBeFalse()
        ->and($result->explanation)->toBe('Not a company.');
});

it('fails when input exceeds max length', function () {
    $driver = new class implements DriverInterface
    {
        public int $callCount = 0;

        public function send(DriverRequest $request): DriverResponse
        {
            $this->callCount++;

            return new DriverResponse(true, 'Valid.');
        }
    };

    $validator = buildAiValidator($driver, maxInputLength: 10);
    $ctx = new ValidationContext(str_repeat('a', 11), 'rule', 'field');

    $result = $validator->validate($ctx);

    expect($result->passed)->toBeFalse()
        ->and($result->explanation)->toBe('Input is too long to validate.')
        ->and($driver->callCount)->toBe(0);
});

it('passes exactly at max length', function () {
    $driver = new class implements DriverInterface
    {
        public function send(DriverRequest $request): DriverResponse
        {
            return new DriverResponse(true, 'Valid.');
        }
    };

    $validator = buildAiValidator($driver, maxInputLength: 10);
    $ctx = new ValidationContext(str_repeat('a', 10), 'rule', 'field');

    $result = $validator->validate($ctx);

    expect($result->passed)->toBeTrue();
});

it('passes context overrides to driver request', function () {
    $captured = null;
    $driver = new class($captured) implements DriverInterface
    {
        public function __construct(private ?DriverRequest &$captured) {}

        public function send(DriverRequest $request): DriverResponse
        {
            $this->captured = $request;

            return new DriverResponse(true, 'Valid.');
        }
    };

    $validator = buildAiValidator($driver);
    $ctx = new ValidationContext('value', 'rule', 'field');
    $ctx->provider = 'anthropic';
    $ctx->model = 'claude-3';
    $ctx->timeout = 30;

    $validator->validate($ctx);

    expect($captured->provider)->toBe('anthropic')
        ->and($captured->model)->toBe('claude-3')
        ->and($captured->timeout)->toBe(30);
});

it('passes null overrides when context has no overrides', function () {
    $captured = null;
    $driver = new class($captured) implements DriverInterface
    {
        public function __construct(private ?DriverRequest &$captured) {}

        public function send(DriverRequest $request): DriverResponse
        {
            $this->captured = $request;

            return new DriverResponse(true, 'Valid.');
        }
    };

    $validator = buildAiValidator($driver);
    $ctx = new ValidationContext('value', 'rule', 'field');

    $validator->validate($ctx);

    expect($captured->provider)->toBeNull()
        ->and($captured->model)->toBeNull()
        ->and($captured->timeout)->toBeNull();
});

it('applies options as middleware before driver call', function () {
    $captured = null;
    $driver = new class($captured) implements DriverInterface
    {
        public function __construct(private ?DriverRequest &$captured) {}

        public function send(DriverRequest $request): DriverResponse
        {
            $this->captured = $request;

            return new DriverResponse(true, 'Valid.');
        }
    };

    $validator = buildAiValidator($driver);
    $ctx = new ValidationContext('value', 'rule', 'field');

    $validator->validate($ctx, [new Using('openai', 'gpt-4'), new Timeout(45)]);

    expect($captured->provider)->toBe('openai')
        ->and($captured->model)->toBe('gpt-4')
        ->and($captured->timeout)->toBe(45);
});

it('wraps driver exceptions in DriverException', function () {
    $driver = new class implements DriverInterface
    {
        public function send(DriverRequest $request): DriverResponse
        {
            throw new RuntimeException('API connection refused');
        }
    };

    $validator = buildAiValidator($driver);
    $ctx = new ValidationContext('value', 'rule', 'field');

    $validator->validate($ctx);
})->throws(DriverException::class, 'AI driver request failed.');

it('preserves original exception in DriverException', function () {
    $original = new RuntimeException('API connection refused');
    $driver = new class($original) implements DriverInterface
    {
        public function __construct(private RuntimeException $exception) {}

        public function send(DriverRequest $request): DriverResponse
        {
            throw $this->exception;
        }
    };

    $validator = buildAiValidator($driver);
    $ctx = new ValidationContext('value', 'rule', 'field');

    try {
        $validator->validate($ctx);
    } catch (DriverException $e) {
        expect($e->getPrevious())->toBe($original);

        return;
    }

    test()->fail('Expected DriverException was not thrown.');
});

function buildAiValidator(DriverInterface $driver, int $maxInputLength = 5000): AiValidator
{
    $noopCache = new class implements ResultCacheInterface
    {
        public function get(CacheKey $key): ?ValidationResult
        {
            return null;
        }

        public function put(CacheKey $key, ValidationResult $result, int $ttl): void {}
    };

    return new AiValidator(
        driver: $driver,
        promptBuilder: new PromptBuilder,
        cacheMiddleware: new CacheMiddleware($noopCache, new CacheConfig(false, 3600, null, 'test')),
        rateLimitMiddleware: new RateLimitMiddleware(new RateLimitConfig(false, 60, 60)),
        maxInputLength: $maxInputLength,
    );
}
