<?php

namespace VirtualTestNamespace\Factories\Advanced;

class QualifiedFactory
{
    public function build(): object
    {
        return new \VirtualTestNamespace\Services\OldService();
    }
}
