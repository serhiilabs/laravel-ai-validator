<?php

declare(strict_types=1);

namespace SerhiiLabs\AiValidator\Drivers;

use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Schema\BooleanSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use SerhiiLabs\AiValidator\Contracts\DriverInterface;
use SerhiiLabs\AiValidator\ValueObjects\DriverRequest;
use SerhiiLabs\AiValidator\ValueObjects\DriverResponse;

final class PrismDriver implements DriverInterface
{
    private ?ObjectSchema $schema = null;

    public function __construct(
        private string $provider,
        private string $model,
        private int $timeout = 15,
    ) {}

    public function send(DriverRequest $request): DriverResponse
    {
        $response = Prism::structured()
            ->using(Provider::from($request->provider ?? $this->provider), $request->model ?? $this->model)
            ->withSchema($this->schema())
            ->withSystemPrompt($request->systemPrompt)
            ->withPrompt($request->userPrompt)
            ->withClientOptions(['timeout' => $request->timeout ?? $this->timeout])
            ->asStructured();

        /** @var array{passed?: bool, explanation?: string} $data */
        $data = $response->structured ?? [];

        return new DriverResponse(
            passed: ($data['passed'] ?? false) === true,
            explanation: (string) ($data['explanation'] ?? 'Validation failed.'),
        );
    }

    private function schema(): ObjectSchema
    {
        return $this->schema ??= new ObjectSchema(
            name: 'validation_result',
            description: 'Result of validating user input against the given criteria',
            properties: [
                new BooleanSchema('passed', 'Whether the input passes the validation criteria'),
                new StringSchema('explanation', 'Brief explanation of why the input passed or failed'),
            ],
            requiredFields: ['passed', 'explanation'],
        );
    }
}
