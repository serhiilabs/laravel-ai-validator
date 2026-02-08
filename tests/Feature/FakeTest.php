<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Validator;
use SerhiiLabs\AiValidator\AiRule;
use SerhiiLabs\AiValidator\Testing\AiValidatorFake;
use SerhiiLabs\AiValidator\ValueObjects\ValidationResult;

afterEach(function () {
    AiValidatorFake::reset();
});

it('passes all validations with AiValidatorFake::pass()', function () {
    AiValidatorFake::pass();

    $validator = Validator::make(
        ['company' => 'anything'],
        ['company' => [new AiRule('real company name')]],
    );

    expect($validator->passes())->toBeTrue();
});

it('fails all validations with AiValidatorFake::fail()', function () {
    AiValidatorFake::fail('Not valid.');

    $validator = Validator::make(
        ['company' => 'anything'],
        ['company' => [new AiRule('real company name')]],
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->first('company'))->toBe('Not valid.');
});

it('supports per-description expectations', function () {
    $fake = AiValidatorFake::pass();
    $fake->expectDescription('not spam', ValidationResult::failed('Looks like spam.'));

    $validator = Validator::make(
        ['name' => 'Acme', 'comment' => 'Buy now!!!'],
        [
            'name' => [new AiRule('real company name')],
            'comment' => [new AiRule('not spam')],
        ],
    );

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('name'))->toBeFalse()
        ->and($validator->errors()->first('comment'))->toBe('Looks like spam.');
});

it('tracks call count', function () {
    $fake = AiValidatorFake::pass();

    Validator::make(
        ['a' => 'x', 'b' => 'y'],
        [
            'a' => [new AiRule('rule a')],
            'b' => [new AiRule('rule b')],
        ],
    )->passes();

    $fake->assertCalledTimes(2);
});

it('asserts called with specific description', function () {
    $fake = AiValidatorFake::pass();

    Validator::make(
        ['name' => 'Acme'],
        ['name' => [new AiRule('real company name')]],
    )->passes();

    $fake->assertCalledWithDescription('real company name');
});

it('asserts called with specific value', function () {
    $fake = AiValidatorFake::pass();

    Validator::make(
        ['name' => 'Acme Corp'],
        ['name' => [new AiRule('real company name')]],
    )->passes();

    $fake->assertCalledWithValue('Acme Corp');
});

it('asserts not called when value is empty', function () {
    $fake = AiValidatorFake::pass();

    Validator::make(
        ['name' => ''],
        ['name' => ['nullable', new AiRule('real company name')]],
    )->passes();

    $fake->assertNotCalled();
});

it('throws LogicException when no default result and no matching expectation', function () {
    $fake = new AiValidatorFake;
    $fake->register();

    Validator::make(
        ['name' => 'test'],
        ['name' => [new AiRule('unmatched rule')]],
    )->passes();
})->throws(LogicException::class, 'no expectation matched');

it('exposes call log with full details', function () {
    $fake = AiValidatorFake::pass();

    Validator::make(
        ['name' => 'Acme'],
        ['name' => [new AiRule('real company name')]],
    )->passes();

    $log = $fake->callLog();

    expect($log)->toHaveCount(1)
        ->and($log[0]['value'])->toBe('Acme')
        ->and($log[0]['description'])->toBe('real company name')
        ->and($log[0]['attribute'])->toBe('name');
});

it('assertCalledWithDescription throws for non-matching description', function () {
    $fake = AiValidatorFake::pass();

    Validator::make(
        ['name' => 'Acme'],
        ['name' => [new AiRule('real company name')]],
    )->passes();

    $fake->assertCalledWithDescription('nonexistent');
})->throws(PHPUnit\Framework\ExpectationFailedException::class);

it('assertCalledWithValue throws for non-matching value', function () {
    $fake = AiValidatorFake::pass();

    Validator::make(
        ['name' => 'Acme'],
        ['name' => [new AiRule('real company name')]],
    )->passes();

    $fake->assertCalledWithValue('nonexistent');
})->throws(PHPUnit\Framework\ExpectationFailedException::class);

it('assertCalledTimes throws when count differs', function () {
    $fake = AiValidatorFake::pass();

    Validator::make(
        ['name' => 'Acme'],
        ['name' => [new AiRule('real company name')]],
    )->passes();

    $fake->assertCalledTimes(999);
})->throws(PHPUnit\Framework\ExpectationFailedException::class);
