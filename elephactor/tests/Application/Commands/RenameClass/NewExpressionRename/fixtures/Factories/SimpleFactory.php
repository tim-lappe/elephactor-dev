<?php

namespace VirtualTestNamespace\Factories;

use VirtualTestNamespace\Services\OldService;

class SimpleFactory
{
    public function build(): object
    {
        return new OldService();
    }
}
