<?php

declare(strict_types=1);

namespace SerhiiLabs\AiValidator\Testing;

use LogicException;
use PHPUnit\Framework\Assert;
use SerhiiLabs\AiValidator\Contracts\AiValidatorInterface;
use SerhiiLabs\AiValidator\Contracts\RuleOptionInterface;
use SerhiiLabs\AiValidator\ValueObjects\ValidationContext;
use SerhiiLabs\AiValidator\ValueObjects\ValidationResult;

final class AiValidatorFake implements AiValidatorInterface
{
    /** @var array<string, ValidationResult> */
    private array $expectations = [];

    private ?ValidationResult $defaultResult = null;

    /** @var list<array{value: string, description: string, attribute: string, options: list<RuleOptionInterface>}> */
    private array $callLog = [];

    public static function pass(): self
    {
        $fake = new self;
        $fake->defaultResult = ValidationResult::passed();
        $fake->register();

        return $fake;
    }

    public static function fail(string $explanation = 'Validation failed.'): self
    {
        $fake = new self;
        $fake->defaultResult = ValidationResult::failed($explanation);
        $fake->register();

        return $fake;
    }

    public static function reset(): void
    {
        app()->forgetInstance(AiValidatorInterface::class);
    }

    public function register(): void
    {
        app()->instance(AiValidatorInterface::class, $this);
    }

    public function expectDescription(string $description, ValidationResult $result): self
    {
        $this->expectations[$description] = $result;

        return $this;
    }

    /**
     * @param  list<RuleOptionInterface>  $options
     */
    public function validate(ValidationContext $ctx, array $options = []): ValidationResult
    {
        $this->callLog[] = [
            'value' => $ctx->value,
            'description' => $ctx->description,
            'attribute' => $ctx->attribute,
            'options' => $options,
        ];

        if (isset($this->expectations[$ctx->description])) {
            return $this->expectations[$ctx->description];
        }

        return $this->defaultResult ?? throw new LogicException(
            'AiValidatorFake: no expectation matched and no default result set. Use pass() or fail() to configure.',
        );
    }

    public function assertCalledTimes(int $count): void
    {
        Assert::assertCount(
            $count,
            $this->callLog,
            sprintf(
                'Expected %d AI validation calls, got %d.',
                $count,
                count($this->callLog),
            ),
        );
    }

    public function assertCalledWithDescription(string $description): void
    {
        $found = array_filter(
            $this->callLog,
            fn (array $call): bool => $call['description'] === $description,
        );

        Assert::assertNotEmpty(
            $found,
            sprintf(
                'Expected AI validation to be called with description "%s".',
                $description,
            ),
        );
    }

    public function assertCalledWithValue(string $value): void
    {
        $found = array_filter(
            $this->callLog,
            fn (array $call): bool => $call['value'] === $value,
        );

        Assert::assertNotEmpty(
            $found,
            sprintf(
                'Expected AI validation to be called with value "%s".',
                $value,
            ),
        );
    }

    public function assertNotCalled(): void
    {
        Assert::assertEmpty(
            $this->callLog,
            sprintf(
                'Expected no AI validation calls, got %d.',
                count($this->callLog),
            ),
        );
    }

    /**
     * @return list<array{value: string, description: string, attribute: string, options: list<RuleOptionInterface>}>
     */
    public function callLog(): array
    {
        return $this->callLog;
    }
}
