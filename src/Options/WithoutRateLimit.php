<?php

declare(strict_types=1);

namespace SerhiiLabs\AiValidator\Options;

use Closure;
use SerhiiLabs\AiValidator\Contracts\RuleOptionInterface;
use SerhiiLabs\AiValidator\ValueObjects\ValidationContext;
use SerhiiLabs\AiValidator\ValueObjects\ValidationResult;

final readonly class WithoutRateLimit implements RuleOptionInterface
{
    public function handle(ValidationContext $ctx, Closure $next): ValidationResult
    {
        $ctx->rateLimitEnabled = false;

        return $next($ctx);
    }
}
