<?php

declare(strict_types=1);

namespace SerhiiLabs\AiValidator\Contracts;

use SerhiiLabs\AiValidator\ValueObjects\ValidationContext;
use SerhiiLabs\AiValidator\ValueObjects\ValidationResult;

interface AiValidatorInterface
{
    /**
     * @param  list<RuleOptionInterface>  $options
     */
    public function validate(ValidationContext $ctx, array $options = []): ValidationResult;
}
