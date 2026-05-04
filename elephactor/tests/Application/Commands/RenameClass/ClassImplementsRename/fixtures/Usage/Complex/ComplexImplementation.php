<?php

namespace VirtualTestNamespace\Usage\Complex;

use VirtualTestNamespace\Contracts\AdditionalInterface;

class ComplexImplementation implements \VirtualTestNamespace\Contracts\OldContract, AdditionalInterface
{
    public function run(): void
    {
    }
}
