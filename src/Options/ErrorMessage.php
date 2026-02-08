<?php

declare(strict_types=1);

namespace SerhiiLabs\AiValidator\Options;

use Closure;
use InvalidArgumentException;
use SerhiiLabs\AiValidator\Contracts\RuleOptionInterface;
use SerhiiLabs\AiValidator\ValueObjects\ValidationContext;
use SerhiiLabs\AiValidator\ValueObjects\ValidationResult;

final readonly class ErrorMessage implements RuleOptionInterface
{
    public function __construct(private string $message)
    {
        if (trim($message) === '') {
            throw new InvalidArgumentException('Error message cannot be empty.');
        }
    }

    public function handle(ValidationContext $ctx, Closure $next): ValidationResult
    {
        $result = $next($ctx);

        return $result->passed ? $result : ValidationResult::failed($this->message);
    }
}
