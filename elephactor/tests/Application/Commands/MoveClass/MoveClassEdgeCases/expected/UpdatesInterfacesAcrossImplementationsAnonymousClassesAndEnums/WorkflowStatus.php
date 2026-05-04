<?php

namespace VirtualTestNamespace\State;

use VirtualTestNamespace\Protocols\FooContract;

enum WorkflowStatus implements FooContract
{
    case STARTED;

    public function run(): void
    {
    }
}
