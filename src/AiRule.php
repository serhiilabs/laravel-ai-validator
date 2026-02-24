<?php

declare(strict_types=1);

namespace SerhiiLabs\AiValidator;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use InvalidArgumentException;
use JsonException;
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
use SerhiiLabs\AiValidator\ValueObjects\ValidationContext;
use SerhiiLabs\AiValidator\ValueObjects\ValidationResult;

final class AiRule implements ValidationRule
{
    /** @var list<RuleOptionInterface> */
    private array $options = [];

    public function __construct(
        private readonly string $description,
    ) {
        if (trim($description) === '') {
            throw new InvalidArgumentException('Validation description cannot be empty.');
        }
    }

    public static function make(string $description): self
    {
        return new self($description);
    }

    public static function preset(string $name): self
    {
        /** @var array<string, string> $presets */
        $presets = config('ai-validator.presets', []);

        if (! isset($presets[$name])) {
            throw new InvalidArgumentException("Unknown preset: $name.");
        }

        if (trim($presets[$name]) === '') {
            throw new InvalidArgumentException("Preset '$name' has an empty description.");
        }

        return self::make($presets[$name]);
    }

    public function using(string $provider, string $model): self
    {
        $this->options[] = new Using($provider, $model);

        return $this;
    }

    public function timeout(int $seconds): self
    {
        $this->options[] = new Timeout($seconds);

        return $this;
    }

    public function cacheTtl(int $seconds): self
    {
        $this->options[] = new CacheTtl($seconds);

        return $this;
    }

    public function withoutCache(): self
    {
        $this->options[] = new WithoutCache;

        return $this;
    }

    public function withoutRateLimit(): self
    {
        $this->options[] = new WithoutRateLimit;

        return $this;
    }

    public function errorMessage(string $message): self
    {
        $this->options[] = new ErrorMessage($message);

        return $this;
    }

    public function with(RuleOptionInterface $option): self
    {
        $this->options[] = $option;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        try {
            $stringValue = $this->castToString($value);
        } catch (JsonException) {
            $fail('The :attribute could not be validated.');

            return;
        }

        $ctx = new ValidationContext(
            value: $stringValue,
            description: $this->description,
            attribute: $attribute,
        );

        $result = $this->resolveResult($ctx);

        if (! $result->passed) {
            $fail($result->explanation);
        }
    }

    private function resolveResult(ValidationContext $ctx): ValidationResult
    {
        try {
            return app(AiValidatorInterface::class)->validate($ctx, $this->options);
        } catch (RateLimitExceededException $e) {
            return ValidationResult::failed($e->getMessage());
        } catch (DriverException) {
            return ValidationResult::failed('AI validation is temporarily unavailable. Please try again shortly.');
        }
    }

    /** @throws JsonException */
    private function castToString(mixed $value): string
    {
        return is_string($value) ? $value : (string) json_encode($value, JSON_THROW_ON_ERROR);
    }
}
