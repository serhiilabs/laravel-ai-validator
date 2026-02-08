<?php

declare(strict_types=1);

namespace SerhiiLabs\AiValidator\Contracts;

use SerhiiLabs\AiValidator\ValueObjects\DriverRequest;
use SerhiiLabs\AiValidator\ValueObjects\DriverResponse;

interface DriverInterface
{
    public function send(DriverRequest $request): DriverResponse;
}
