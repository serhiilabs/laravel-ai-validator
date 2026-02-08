<?php

declare(strict_types=1);

namespace SerhiiLabs\AiValidator\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Prism\Prism\PrismServiceProvider;
use SerhiiLabs\AiValidator\AiValidatorServiceProvider;
use SerhiiLabs\AiValidator\Drivers\PrismDriver;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            PrismServiceProvider::class,
            AiValidatorServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('ai-validator.driver', PrismDriver::class);
        $app['config']->set('ai-validator.provider', 'openai');
        $app['config']->set('ai-validator.model', 'gpt-4o-mini');
    }
}
