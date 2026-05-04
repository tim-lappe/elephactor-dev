<?php

namespace VirtualTestNamespace\State;

use VirtualTestNamespace\Contracts\FooContract;

enum WorkflowStatus implements FooContract
{
    case STARTED;

    public function run(): void
    {
    }
}
