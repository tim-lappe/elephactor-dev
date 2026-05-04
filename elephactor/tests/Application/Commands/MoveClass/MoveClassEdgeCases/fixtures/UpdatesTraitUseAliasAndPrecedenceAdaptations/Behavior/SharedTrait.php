<?php

namespace VirtualTestNamespace\Behavior;

trait SharedTrait
{
    public function helper(): string
    {
        return 'shared';
    }
}
