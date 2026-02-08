<?php

declare(strict_types=1);

arch('source files use strict types')
    ->expect('SerhiiLabs\AiValidator')
    ->toUseStrictTypes();

arch('no debugging functions in source')
    ->expect(['dd', 'dump', 'ray', 'var_dump', 'print_r'])
    ->not->toBeUsed();

arch('contracts are interfaces')
    ->expect('SerhiiLabs\AiValidator\Contracts')
    ->toBeInterfaces();

arch('implementations are final')
    ->expect('SerhiiLabs\AiValidator')
    ->classes()
    ->toBeFinal();
