<?php

declare(strict_types=1);

namespace SerhiiLabs\AiValidator\Options;

use Closure;
use InvalidArgumentException;
use SerhiiLabs\AiValidator\Contracts\RuleOptionInterface;
use SerhiiLabs\AiValidator\ValueObjects\ValidationContext;
use SerhiiLabs\AiValidator\ValueObjects\ValidationResult;

final readonly class Using implements RuleOptionInterface
{
    public function __construct(
        private string $provider,
        private string $model,
    ) {
        if (trim($provider) === '' || trim($model) === '') {
            throw new InvalidArgumentException('Provider and model cannot be empty.');
        }
    }

    public function handle(ValidationContext $ctx, Closure $next): ValidationResult
    {
        $ctx->provider = $this->provider;
        $ctx->model = $this->model;

        return $next($ctx);
    }
}
