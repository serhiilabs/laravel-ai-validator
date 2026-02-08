<?php

declare(strict_types=1);

namespace SerhiiLabs\AiValidator;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use RuntimeException;
use SerhiiLabs\AiValidator\Cache\CacheMiddleware;
use SerhiiLabs\AiValidator\Cache\ResultCache;
use SerhiiLabs\AiValidator\Config\CacheConfig;
use SerhiiLabs\AiValidator\Config\RateLimitConfig;
use SerhiiLabs\AiValidator\Contracts\AiValidatorInterface;
use SerhiiLabs\AiValidator\Contracts\DriverInterface;
use SerhiiLabs\AiValidator\Contracts\ResultCacheInterface;
use SerhiiLabs\AiValidator\Drivers\PrismDriver;
use SerhiiLabs\AiValidator\Prompt\PromptBuilder;
use SerhiiLabs\AiValidator\RateLimit\RateLimitMiddleware;

final class AiValidatorServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/ai-validator.php', 'ai-validator');

        $this->app->singleton(CacheConfig::class, fn (): CacheConfig => CacheConfig::fromConfig());

        $this->app->singleton(ResultCacheInterface::class, fn (): ResultCache => new ResultCache(
            $this->app->make(CacheConfig::class),
        ));

        $this->app
            ->when(PrismDriver::class)
            ->needs('$provider')
            ->give(function (): string {
                /** @var string $provider */
                $provider = config('ai-validator.provider', '');

                return $provider;
            });

        $this->app
            ->when(PrismDriver::class)
            ->needs('$model')
            ->give(function (): string {
                /** @var string $model */
                $model = config('ai-validator.model', '');

                return $model;
            });

        $this->app
            ->when(PrismDriver::class)
            ->needs('$timeout')
            ->give(function (): int {
                /** @var int|string $timeout */
                $timeout = config('ai-validator.timeout', 15);

                return (int) $timeout;
            });

        if (! $this->app->bound(DriverInterface::class)) {
            $this->app->singleton(DriverInterface::class, function (): DriverInterface {
                /** @var class-string<DriverInterface>|null $driverClass */
                $driverClass = config('ai-validator.driver');

                if ($driverClass === null) {
                    throw new RuntimeException(
                        'No AI driver configured. Set the "driver" key in config/ai-validator.php. '
                        .'Example: \'driver\' => \\SerhiiLabs\\AiValidator\\Drivers\\PrismDriver::class'
                    );
                }

                /** @var DriverInterface */
                return $this->app->make($driverClass);
            });
        }

        $this->app->singleton(AiValidatorInterface::class, function (): AiValidator {
            /** @var string|null $systemPrompt */
            $systemPrompt = config('ai-validator.system_prompt');

            /** @var int|string $maxInputLength */
            $maxInputLength = config('ai-validator.max_input_length', 5000);

            return new AiValidator(
                driver: $this->app->make(DriverInterface::class),
                promptBuilder: new PromptBuilder($systemPrompt),
                cacheMiddleware: new CacheMiddleware(
                    $this->app->make(ResultCacheInterface::class),
                    $this->app->make(CacheConfig::class),
                ),
                rateLimitMiddleware: new RateLimitMiddleware(
                    RateLimitConfig::fromConfig(),
                ),
                maxInputLength: (int) $maxInputLength,
            );
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/ai-validator.php' => config_path('ai-validator.php'),
            ], 'ai-validator-config');
        }
    }

    /**
     * @return list<string>
     */
    public function provides(): array
    {
        return [
            DriverInterface::class,
            AiValidatorInterface::class,
            ResultCacheInterface::class,
        ];
    }
}
