<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Validator;
use Prism\Prism\Facades\Prism;
use SerhiiLabs\AiValidator\AiRule;
use SerhiiLabs\AiValidator\Contracts\AiValidatorInterface;

it('passes validation when LLM returns passed true', function () {
    Prism::fake([
        fakeStructuredResponse(true, 'Valid company name.'),
    ]);

    $validator = Validator::make(
        ['company' => 'Acme Corporation'],
        ['company' => ['required', new AiRule('real company name')]],
    );

    expect($validator->passes())->toBeTrue();
});

it('fails validation when LLM returns passed false', function () {
    Prism::fake([
        fakeStructuredResponse(false, 'This appears to be random characters.'),
    ]);

    $validator = Validator::make(
        ['company' => 'asdfghjkl123'],
        ['company' => ['required', new AiRule('real company name')]],
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('company'))
        ->toBe('This appears to be random characters.');
});

it('uses custom error message when provided', function () {
    Prism::fake([
        fakeStructuredResponse(false, 'LLM explanation ignored.'),
    ]);

    $validator = Validator::make(
        ['company' => 'xxx'],
        ['company' => [AiRule::make('real company name')->errorMessage('Invalid company name.')]],
    );

    expect($validator->errors()->first('company'))->toBe('Invalid company name.');
});

it('passes validation for null when field is nullable', function () {
    $validator = Validator::make(
        ['company' => null],
        ['company' => ['nullable', new AiRule('real company name')]],
    );

    expect($validator->passes())->toBeTrue();
});

it('fails validation when input exceeds max length', function () {
    config(['ai-validator.max_input_length' => 10]);
    app()->forgetInstance(AiValidatorInterface::class);

    $validator = Validator::make(
        ['company' => str_repeat('a', 11)],
        ['company' => [new AiRule('real company name')]],
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('company'))
        ->toBe('Input is too long to validate.');
});
