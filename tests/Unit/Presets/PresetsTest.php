<?php

declare(strict_types=1);

use SerhiiLabs\AiValidator\AiRule;
use SerhiiLabs\AiValidator\Testing\AiValidatorFake;

afterEach(fn () => AiValidatorFake::reset());

it('creates preset from config', function () {
    config(['ai-validator.presets' => [
        'bio-check' => 'Must be a professional biography.',
    ]]);

    $fake = AiValidatorFake::pass();

    $rule = AiRule::preset('bio-check');
    $rule->validate('bio', 'Hello world', function () {});

    expect($fake->callLog()[0]['description'])->toBe('Must be a professional biography.');
});

it('chains preset with options', function () {
    config(['ai-validator.presets' => [
        'no-profanity' => 'No profanity or vulgar language.',
    ]]);

    $fake = AiValidatorFake::pass();

    $rule = AiRule::preset('no-profanity')
        ->timeout(30)
        ->errorMessage('Keep it clean.');
    $rule->validate('comment', 'Hello world', function () {});

    $log = $fake->callLog();
    expect($log[0]['options'])->toHaveCount(2);
    expect($log[0]['description'])->toBe('No profanity or vulgar language.');
});

it('throws on unknown preset', function () {
    config(['ai-validator.presets' => []]);

    AiRule::preset('nonexistent');
})->throws(InvalidArgumentException::class, 'Unknown preset: nonexistent.');

it('throws on preset with empty description', function () {
    config(['ai-validator.presets' => ['empty' => '']]);

    AiRule::preset('empty');
})->throws(InvalidArgumentException::class, "Preset 'empty' has an empty description.");

it('throws on preset with whitespace-only description', function () {
    config(['ai-validator.presets' => ['blank' => '   ']]);

    AiRule::preset('blank');
})->throws(InvalidArgumentException::class, "Preset 'blank' has an empty description.");
