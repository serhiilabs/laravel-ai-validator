<?php

declare(strict_types=1);

use SerhiiLabs\AiValidator\AiRule;
use SerhiiLabs\AiValidator\Contracts\AiValidatorInterface;
use SerhiiLabs\AiValidator\Contracts\RuleOptionInterface;
use SerhiiLabs\AiValidator\Exceptions\DriverException;
use SerhiiLabs\AiValidator\Exceptions\RateLimitExceededException;
use SerhiiLabs\AiValidator\Options\CacheTtl;
use SerhiiLabs\AiValidator\Options\ErrorMessage;
use SerhiiLabs\AiValidator\Options\Timeout;
use SerhiiLabs\AiValidator\Options\Using;
use SerhiiLabs\AiValidator\Options\WithoutCache;
use SerhiiLabs\AiValidator\Options\WithoutRateLimit;
use SerhiiLabs\AiValidator\Testing\AiValidatorFake;
use SerhiiLabs\AiValidator\ValueObjects\ValidationContext;
use SerhiiLabs\AiValidator\ValueObjects\ValidationResult;

afterEach(function () {
    AiValidatorFake::reset();
});

it('skips validation for null values', function () {
    $fake = AiValidatorFake::fail('Should not be called.');

    $rule = new AiRule('real company name');
    $failed = false;

    $rule->validate('company', null, function () use (&$failed) {
        $failed = true;
    });

    expect($failed)->toBeFalse();
    $fake->assertNotCalled();
});

it('skips validation for empty strings', function () {
    $fake = AiValidatorFake::fail('Should not be called.');

    $rule = new AiRule('real company name');
    $failed = false;

    $rule->validate('company', '', function () use (&$failed) {
        $failed = true;
    });

    expect($failed)->toBeFalse();
    $fake->assertNotCalled();
});

it('creates rule via static factory', function () {
    $rule = AiRule::make('real company name');

    expect($rule)->toBeInstanceOf(AiRule::class);
});

it('json encodes non-string values', function () {
    $fake = AiValidatorFake::pass();

    $rule = new AiRule('valid tags');
    $rule->validate('tags', ['php', 'laravel'], function () {});

    $log = $fake->callLog();
    expect($log[0]['value'])->toBe('["php","laravel"]');
});

it('throws exception for empty description', function () {
    new AiRule('');
})->throws(InvalidArgumentException::class, 'Validation description cannot be empty.');

it('throws exception for whitespace-only description', function () {
    new AiRule('   ');
})->throws(InvalidArgumentException::class, 'Validation description cannot be empty.');

it('propagates using() option to validator', function () {
    $fake = AiValidatorFake::pass();

    $rule = AiRule::make('test rule')->using('anthropic', 'claude-3');
    $rule->validate('field', 'value', function () {});

    $log = $fake->callLog();
    $usingOption = collect($log[0]['options'])->first(fn ($o) => $o instanceof Using);

    expect($usingOption)->toBeInstanceOf(Using::class);
});

it('propagates timeout() option to validator', function () {
    $fake = AiValidatorFake::pass();

    $rule = AiRule::make('test rule')->timeout(30);
    $rule->validate('field', 'value', function () {});

    $log = $fake->callLog();
    $timeoutOption = collect($log[0]['options'])->first(fn ($o) => $o instanceof Timeout);

    expect($timeoutOption)->toBeInstanceOf(Timeout::class);
});

it('propagates cacheTtl() option to validator', function () {
    $fake = AiValidatorFake::pass();

    $rule = AiRule::make('test rule')->cacheTtl(7200);
    $rule->validate('field', 'value', function () {});

    $log = $fake->callLog();
    $cacheTtlOption = collect($log[0]['options'])->first(fn ($o) => $o instanceof CacheTtl);

    expect($cacheTtlOption)->toBeInstanceOf(CacheTtl::class);
});

it('propagates errorMessage() option to validator', function () {
    $fake = AiValidatorFake::pass();

    $rule = AiRule::make('test rule')->errorMessage('Custom error.');
    $rule->validate('field', 'value', function () {});

    $log = $fake->callLog();
    $option = collect($log[0]['options'])->first(fn ($o) => $o instanceof ErrorMessage);

    expect($option)->toBeInstanceOf(ErrorMessage::class);
});

it('propagates withoutCache() option to validator', function () {
    $fake = AiValidatorFake::pass();

    $rule = AiRule::make('test rule')->withoutCache();
    $rule->validate('field', 'value', function () {});

    $log = $fake->callLog();
    $option = collect($log[0]['options'])->first(fn ($o) => $o instanceof WithoutCache);

    expect($option)->toBeInstanceOf(WithoutCache::class);
});

it('propagates withoutRateLimit() option to validator', function () {
    $fake = AiValidatorFake::pass();

    $rule = AiRule::make('test rule')->withoutRateLimit();
    $rule->validate('field', 'value', function () {});

    $log = $fake->callLog();
    $option = collect($log[0]['options'])->first(fn ($o) => $o instanceof WithoutRateLimit);

    expect($option)->toBeInstanceOf(WithoutRateLimit::class);
});

it('calls fail when json encoding fails', function () {
    AiValidatorFake::pass();

    $rule = new AiRule('test rule');
    $failMessage = null;

    $resource = fopen('php://memory', 'r');
    $rule->validate('field', $resource, function (string $message) use (&$failMessage) {
        $failMessage = $message;
    });
    fclose($resource);

    expect($failMessage)->toBe('The :attribute could not be validated.');
});

it('throws exception for zero timeout', function () {
    AiRule::make('test rule')->timeout(0);
})->throws(InvalidArgumentException::class, 'Timeout must be at least 1 second.');

it('throws exception for negative timeout', function () {
    AiRule::make('test rule')->timeout(-5);
})->throws(InvalidArgumentException::class, 'Timeout must be at least 1 second.');

it('throws exception for zero cacheTtl', function () {
    AiRule::make('test rule')->cacheTtl(0);
})->throws(InvalidArgumentException::class, 'Cache TTL must be at least 1 second.');

it('throws exception for negative cacheTtl', function () {
    AiRule::make('test rule')->cacheTtl(-10);
})->throws(InvalidArgumentException::class, 'Cache TTL must be at least 1 second.');

it('accepts option via with()', function () {
    $fake = AiValidatorFake::pass();

    $rule = AiRule::make('test rule')->with(new Timeout(15));
    $rule->validate('field', 'value', function () {});

    $log = $fake->callLog();
    $timeoutOption = collect($log[0]['options'])->first(fn ($o) => $o instanceof Timeout);

    expect($timeoutOption)->toBeInstanceOf(Timeout::class);
});

it('chains with() alongside explicit methods', function () {
    $fake = AiValidatorFake::pass();

    $rule = AiRule::make('test rule')
        ->timeout(10)
        ->with(new Using('anthropic', 'claude-3'));
    $rule->validate('field', 'value', function () {});

    $log = $fake->callLog();
    expect($log[0]['options'])->toHaveCount(2);
});

it('accepts custom option via with()', function () {
    $fake = AiValidatorFake::pass();

    $customOption = new class implements RuleOptionInterface
    {
        public function handle(ValidationContext $ctx, Closure $next): ValidationResult
        {
            return $next($ctx);
        }
    };

    $rule = AiRule::make('test rule')->with($customOption);
    $rule->validate('field', 'value', function () {});

    $log = $fake->callLog();
    expect($log[0]['options'])->toHaveCount(1);
});

it('catches driver exceptions and fails with generic message', function () {
    $throwing = new class implements AiValidatorInterface
    {
        public function validate(ValidationContext $ctx, array $options = []): ValidationResult
        {
            throw new DriverException('AI driver request failed.');
        }
    };
    app()->instance(AiValidatorInterface::class, $throwing);

    $rule = new AiRule('test rule');
    $failMessage = null;
    $rule->validate('field', 'value', function (string $message) use (&$failMessage) {
        $failMessage = $message;
    });

    expect($failMessage)->toBe('AI validation is temporarily unavailable. Please try again shortly.');
});

it('preserves rate limit message over generic catch', function () {
    $throwing = new class implements AiValidatorInterface
    {
        public function validate(ValidationContext $ctx, array $options = []): ValidationResult
        {
            throw new RateLimitExceededException('Too many AI validation requests. Please try again in 42 seconds.');
        }
    };
    app()->instance(AiValidatorInterface::class, $throwing);

    $rule = new AiRule('test rule');
    $failMessage = null;
    $rule->validate('field', 'value', function (string $message) use (&$failMessage) {
        $failMessage = $message;
    });

    expect($failMessage)->toBe('Too many AI validation requests. Please try again in 42 seconds.');
});

it('validates integer zero', function () {
    $fake = AiValidatorFake::pass();

    $rule = new AiRule('valid input');
    $rule->validate('field', 0, function () {});

    expect($fake->callLog()[0]['value'])->toBe('0');
});

it('validates boolean false', function () {
    $fake = AiValidatorFake::pass();

    $rule = new AiRule('valid input');
    $rule->validate('field', false, function () {});

    expect($fake->callLog()[0]['value'])->toBe('false');
});

it('validates empty array', function () {
    $fake = AiValidatorFake::pass();

    $rule = new AiRule('valid input');
    $rule->validate('field', [], function () {});

    expect($fake->callLog()[0]['value'])->toBe('[]');
});
