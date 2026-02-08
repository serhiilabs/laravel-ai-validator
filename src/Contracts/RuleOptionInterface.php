<?php

declare(strict_types=1);

namespace SerhiiLabs\AiValidator\Contracts;

use Closure;
use SerhiiLabs\AiValidator\ValueObjects\ValidationContext;
use SerhiiLabs\AiValidator\ValueObjects\ValidationResult;

interface RuleOptionInterface
{
    /**
     * @param  Closure(ValidationContext): ValidationResult  $next
     */
    public function handle(ValidationContext $ctx, Closure $next): ValidationResult;
}
