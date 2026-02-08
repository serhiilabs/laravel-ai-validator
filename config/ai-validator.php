<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | AI Driver
    |--------------------------------------------------------------------------
    |
    | The driver class responsible for communicating with the AI provider.
    | Must implement \SerhiiLabs\AiValidator\Contracts\DriverInterface.
    |
    | Built-in: \SerhiiLabs\AiValidator\Drivers\PrismDriver::class (requires prism-php/prism)
    |
    */

    'driver' => null,

    /*
    |--------------------------------------------------------------------------
    | AI Provider
    |--------------------------------------------------------------------------
    |
    | The AI provider and model used for validation.
    | Required when using the built-in PrismDriver.
    |
    | Supported providers (PrismDriver): "openai", "anthropic", "gemini", "ollama", etc.
    |
    | Example .env configuration:
    |   AI_VALIDATOR_PROVIDER=openai
    |   AI_VALIDATOR_MODEL=gpt-4o-mini
    |
    |   AI_VALIDATOR_PROVIDER=anthropic
    |   AI_VALIDATOR_MODEL=claude-haiku-4-5-20251001
    |
    */

    'provider' => env('AI_VALIDATOR_PROVIDER'),

    'model' => env('AI_VALIDATOR_MODEL'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum time in seconds to wait for an AI response.
    |
    */

    'timeout' => (int) env('AI_VALIDATOR_TIMEOUT', 15),

    /*
    |--------------------------------------------------------------------------
    | Input Limits
    |--------------------------------------------------------------------------
    |
    | Maximum input length in characters sent to the AI provider.
    | Inputs exceeding this limit will fail validation.
    |
    */

    'max_input_length' => (int) env('AI_VALIDATOR_MAX_INPUT_LENGTH', 5000),

    /*
    |--------------------------------------------------------------------------
    | System Prompt Override
    |--------------------------------------------------------------------------
    |
    | Override the default system prompt used for validation.
    | Set to null to use the built-in prompt.
    |
    */

    'system_prompt' => null,

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | Caching configuration for AI validation results.
    | Identical inputs with identical rules return cached results.
    |
    */

    'cache' => [
        'enabled' => (bool) env('AI_VALIDATOR_CACHE_ENABLED', true),
        'store' => env('AI_VALIDATOR_CACHE_STORE'),
        'ttl' => (int) env('AI_VALIDATOR_CACHE_TTL', 3600),
        'prefix' => 'ai_validator',
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Rate limiting for AI validation requests to prevent API abuse.
    | Uses Laravel's built-in RateLimiter.
    |
    */

    'rate_limit' => [
        'enabled' => (bool) env('AI_VALIDATOR_RATE_LIMIT_ENABLED', true),
        'max_attempts' => (int) env('AI_VALIDATOR_RATE_LIMIT_MAX_ATTEMPTS', 60),
        'decay_seconds' => (int) env('AI_VALIDATOR_RATE_LIMIT_DECAY_SECONDS', 60),
    ],

];
