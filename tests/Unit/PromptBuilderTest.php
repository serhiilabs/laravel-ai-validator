<?php

declare(strict_types=1);

use SerhiiLabs\AiValidator\Prompt\PromptBuilder;
use SerhiiLabs\AiValidator\ValueObjects\ValidationContext;

it('returns default system prompt', function () {
    $builder = new PromptBuilder;

    $prompt = $builder->systemPrompt();

    expect($prompt)->toContain('You are a strict form input validator');
});

it('returns custom system prompt when override is set', function () {
    $builder = new PromptBuilder('Custom system prompt.');

    expect($builder->systemPrompt())->toBe('Custom system prompt.');
});

it('builds user prompt with sanitized attribute', function () {
    $ctx = new ValidationContext(
        value: 'Acme Corp',
        description: 'real company name',
        attribute: 'company_name',
    );

    $builder = new PromptBuilder;
    $prompt = $builder->userPrompt($ctx);

    expect($prompt)
        ->toContain('"company_name" field')
        ->toContain('Validation criteria: real company name')
        ->toContain('<input>Acme Corp</input>');
});

it('strips invalid characters from attribute name', function () {
    $ctx = new ValidationContext(
        value: 'test',
        description: 'test rule',
        attribute: 'field<script>alert(1)</script>',
    );

    $builder = new PromptBuilder;
    $prompt = $builder->userPrompt($ctx);

    expect($prompt)
        ->toContain('"fieldscriptalert1script" field')
        ->not->toContain('<script>');
});
