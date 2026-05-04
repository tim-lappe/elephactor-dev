<?php

namespace VirtualTestNamespace\AnonymousUsage\Interfaces;

class InterfaceAnonymousFactory
{
    public function create(): object
    {
        return new class implements \VirtualTestNamespace\Anonymous\OldAnonymousInterface
        {
            public function run(): void
            {
            }
        };
    }
}
