<?php

namespace VirtualTestNamespace\Services;

use VirtualTestNamespace\Contracts\FooContract;

final class ImplementsContract implements FooContract
{
    public function __construct(private FooContract $contract)
    {
    }

    public function transform(FooContract $input): FooContract
    {
        if ($input instanceof FooContract) {
            return $input;
        }

        return new class($input) implements FooContract
        {
            public function __construct(private FooContract $decorated)
            {
            }

            public function run(): void
            {
                $this->decorated->run();
            }
        };
    }

    public function run(): void
    {
        $this->contract->run();
    }
}
