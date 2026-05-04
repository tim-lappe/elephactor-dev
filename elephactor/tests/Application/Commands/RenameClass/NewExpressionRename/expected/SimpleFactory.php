<?php

namespace VirtualTestNamespace\Factories;

use VirtualTestNamespace\Services\NewService;

class SimpleFactory
{
    public function build(): object
    {
        return new NewService();
    }
}
