<?php

declare(strict_types=1);

use Prism\Prism\Enums\FinishReason;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Structured\Response as StructuredResponse;
use Prism\Prism\ValueObjects\Meta;
use Prism\Prism\ValueObjects\Usage;
use SerhiiLabs\AiValidator\Drivers\PrismDriver;
use SerhiiLabs\AiValidator\ValueObjects\DriverRequest;

it('returns passed response from LLM', function () {
    Prism::fake([fakeStructuredResponse(true, 'Looks good.')]);

    $driver = new PrismDriver('openai', 'gpt-4o-mini');
    $response = $driver->send(new DriverRequest(
        systemPrompt: 'You are a validator.',
        userPrompt: 'Validate this.',
    ));

    expect($response->passed)->toBeTrue()
        ->and($response->explanation)->toBe('Looks good.');
});

it('returns failed response from LLM', function () {
    Prism::fake([fakeStructuredResponse(false, 'Invalid input.')]);

    $driver = new PrismDriver('openai', 'gpt-4o-mini');
    $response = $driver->send(new DriverRequest(
        systemPrompt: 'You are a validator.',
        userPrompt: 'Validate this.',
    ));

    expect($response->passed)->toBeFalse()
        ->and($response->explanation)->toBe('Invalid input.');
});

it('defaults to false when passed key is missing', function () {
    Prism::fake([new StructuredResponse(
        steps: collect([]),
        text: '',
        structured: ['explanation' => 'Some text'],
        finishReason: FinishReason::Stop,
        usage: new Usage(50, 20),
        meta: new Meta('fake-id', 'fake-model'),
    )]);

    $driver = new PrismDriver('openai', 'gpt-4o-mini');
    $response = $driver->send(new DriverRequest(
        systemPrompt: 'System.',
        userPrompt: 'Prompt.',
    ));

    expect($response->passed)->toBeFalse()
        ->and($response->explanation)->toBe('Some text');
});

it('defaults explanation when key is missing', function () {
    Prism::fake([new StructuredResponse(
        steps: collect([]),
        text: '',
        structured: ['passed' => true],
        finishReason: FinishReason::Stop,
        usage: new Usage(50, 20),
        meta: new Meta('fake-id', 'fake-model'),
    )]);

    $driver = new PrismDriver('openai', 'gpt-4o-mini');
    $response = $driver->send(new DriverRequest(
        systemPrompt: 'System.',
        userPrompt: 'Prompt.',
    ));

    expect($response->passed)->toBeTrue()
        ->and($response->explanation)->toBe('Validation failed.');
});

it('uses request overrides when provided', function () {
    Prism::fake([fakeStructuredResponse(true, 'Valid.')]);

    $driver = new PrismDriver('openai', 'gpt-4o-mini', 15);
    $response = $driver->send(new DriverRequest(
        systemPrompt: 'System.',
        userPrompt: 'Prompt.',
        provider: 'anthropic',
        model: 'claude-3',
        timeout: 30,
    ));

    expect($response->passed)->toBeTrue();
});

it('handles empty structured array', function () {
    Prism::fake([new StructuredResponse(
        steps: collect([]),
        text: '',
        structured: [],
        finishReason: FinishReason::Stop,
        usage: new Usage(50, 20),
        meta: new Meta('fake-id', 'fake-model'),
    )]);

    $driver = new PrismDriver('openai', 'gpt-4o-mini');
    $response = $driver->send(new DriverRequest(
        systemPrompt: 'System.',
        userPrompt: 'Prompt.',
    ));

    expect($response->passed)->toBeFalse()
        ->and($response->explanation)->toBe('Validation failed.');
});
