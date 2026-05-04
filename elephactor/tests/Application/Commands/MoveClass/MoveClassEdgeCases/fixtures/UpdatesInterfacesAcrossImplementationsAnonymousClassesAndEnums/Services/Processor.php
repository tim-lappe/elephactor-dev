<?php

namespace VirtualTestNamespace\Services;

use VirtualTestNamespace\Contracts\FooContract;

final class Processor
{
    public function __construct(private FooContract $contract)
    {
    }

    public function execute(FooContract $argument): FooContract
    {
        if ($argument instanceof FooContract) {
            return $argument;
        }

        return $this->contract;
    }
}
