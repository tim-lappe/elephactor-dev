<?php

namespace VirtualTestNamespace\Services;

use VirtualTestNamespace\Protocols\FooContract;

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
