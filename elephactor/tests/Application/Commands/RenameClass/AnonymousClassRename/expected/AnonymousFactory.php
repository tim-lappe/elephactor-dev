<?php

namespace VirtualTestNamespace\AnonymousUsage;

use VirtualTestNamespace\Anonymous\NewAnonymousBase;

class AnonymousFactory
{
    public function create(): object
    {
        return new class extends NewAnonymousBase
        {
            public function marker(): string
            {
                return 'extended';
            }
        };
    }
}
