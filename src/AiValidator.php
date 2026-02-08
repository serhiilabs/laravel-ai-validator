<?php

declare(strict_types=1);

namespace SerhiiLabs\AiValidator;

use Closure;
use SerhiiLabs\AiValidator\Cache\CacheMiddleware;
use SerhiiLabs\AiValidator\Contracts\AiValidatorInterface;
use SerhiiLabs\AiValidator\Contracts\DriverInterface;
use SerhiiLabs\AiValidator\Contracts\RuleOptionInterface;
use SerhiiLabs\AiValidator\Exceptions\DriverException;
use SerhiiLabs\AiValidator\Prompt\PromptBuilder;
use SerhiiLabs\AiValidator\RateLimit\RateLimitMiddleware;
use SerhiiLabs\AiValidator\ValueObjects\DriverRequest;
use SerhiiLabs\AiValidator\ValueObjects\ValidationContext;
use SerhiiLabs\AiValidator\ValueObjects\ValidationResult;
use Throwable;

final class AiValidator implements AiValidatorInterface
{
    public function __construct(
        private DriverInterface $driver,
        private PromptBuilder $promptBuilder,
        private CacheMiddleware $cacheMiddleware,
        private RateLimitMiddleware $rateLimitMiddleware,
        private int $maxInputLength = 5000,
    ) {}

    /**
     * @param  list<RuleOptionInterface>  $options
     */
    public function validate(ValidationContext $ctx, array $options = []): ValidationResult
    {
        if (mb_strlen($ctx->value) > $this->maxInputLength) {
            return ValidationResult::failed('Input is too long to validate.');
        }

        $middleware = [...$options, $this->cacheMiddleware, $this->rateLimitMiddleware];

        $pipeline = array_reduce(
            array_reverse($middleware),
            fn (Closure $next, RuleOptionInterface $option): Closure => fn (ValidationContext $ctx): ValidationResult => $option->handle($ctx, $next),
            fn (ValidationContext $ctx): ValidationResult => $this->executeDriver($ctx),
        );

        return $pipeline($ctx);
    }

    private function executeDriver(ValidationContext $ctx): ValidationResult
    {
        $request = new DriverRequest(
            systemPrompt: $this->promptBuilder->systemPrompt(),
            userPrompt: $this->promptBuilder->userPrompt($ctx),
            provider: $ctx->provider,
            model: $ctx->model,
            timeout: $ctx->timeout,
        );

        try {
            $response = $this->driver->send($request);
        } catch (Throwable $e) {
            throw new DriverException('AI driver request failed.', previous: $e);
        }

        return new ValidationResult(
            passed: $response->passed,
            explanation: $response->explanation,
        );
    }
}
