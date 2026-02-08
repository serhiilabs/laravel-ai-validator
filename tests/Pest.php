<?php

declare(strict_types=1);

use Prism\Prism\Enums\FinishReason;
use Prism\Prism\Structured\Response as StructuredResponse;
use Prism\Prism\ValueObjects\Meta;
use Prism\Prism\ValueObjects\Usage;
use SerhiiLabs\AiValidator\Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit');

function fakeStructuredResponse(bool $passed, string $explanation): StructuredResponse
{
    return new StructuredResponse(
        steps: collect([]),
        text: '',
        structured: ['passed' => $passed, 'explanation' => $explanation],
        finishReason: FinishReason::Stop,
        usage: new Usage(50, 20),
        meta: new Meta('fake-id', 'fake-model'),
    );
}
