<?php

namespace VirtualTestNamespace\Usage\Complex;

use VirtualTestNamespace\Contracts\AdditionalInterface;

class ComplexImplementation implements \VirtualTestNamespace\Contracts\NewContract, AdditionalInterface
{
    public function run(): void
    {
    }
}
