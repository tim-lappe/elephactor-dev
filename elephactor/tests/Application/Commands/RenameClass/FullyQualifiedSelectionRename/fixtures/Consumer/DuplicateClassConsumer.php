<?php

namespace VirtualTestNamespace\Consumer;

use VirtualTestNamespace\Utility\Primary\DuplicateClass;

final class DuplicateClassConsumer
{
    public function build(): DuplicateClass
    {
        return new DuplicateClass();
    }
}
