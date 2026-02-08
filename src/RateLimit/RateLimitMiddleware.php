<?php

declare(strict_types=1);

namespace SerhiiLabs\AiValidator\RateLimit;

use Closure;
use Illuminate\Support\Facades\RateLimiter;
use SerhiiLabs\AiValidator\Config\RateLimitConfig;
use SerhiiLabs\AiValidator\Contracts\RuleOptionInterface;
use SerhiiLabs\AiValidator\Exceptions\RateLimitExceededException;
use SerhiiLabs\AiValidator\ValueObjects\ValidationContext;
use SerhiiLabs\AiValidator\ValueObjects\ValidationResult;

final class RateLimitMiddleware implements RuleOptionInterface
{
    public function __construct(private RateLimitConfig $config) {}

    public function handle(ValidationContext $ctx, Closure $next): ValidationResult
    {
        if (! $this->config->enabled || ! $ctx->rateLimitEnabled) {
            return $next($ctx);
        }

        $key = 'ai_validator';

        if (! RateLimiter::attempt($key, $this->config->maxAttempts, fn () => null, $this->config->decaySeconds)) {
            throw new RateLimitExceededException(
                sprintf('Too many AI validation requests. Please try again in %d seconds.', RateLimiter::availableIn($key))
            );
        }

        return $next($ctx);
    }
}
