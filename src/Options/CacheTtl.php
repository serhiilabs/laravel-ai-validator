<?php

declare(strict_types=1);

namespace SerhiiLabs\AiValidator\Options;

use Closure;
use InvalidArgumentException;
use SerhiiLabs\AiValidator\Contracts\RuleOptionInterface;
use SerhiiLabs\AiValidator\ValueObjects\ValidationContext;
use SerhiiLabs\AiValidator\ValueObjects\ValidationResult;

final readonly class CacheTtl implements RuleOptionInterface
{
    public function __construct(private int $seconds)
    {
        if ($seconds < 1) {
            throw new InvalidArgumentException('Cache TTL must be at least 1 second.');
        }
    }

    public function handle(ValidationContext $ctx, Closure $next): ValidationResult
    {
        $ctx->cacheTtl = $this->seconds;

        return $next($ctx);
    }
}
