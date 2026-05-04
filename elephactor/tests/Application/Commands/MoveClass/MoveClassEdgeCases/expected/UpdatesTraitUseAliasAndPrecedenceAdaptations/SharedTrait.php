<?php

namespace VirtualTestNamespace\Refactored\Mixins;

trait SharedTrait
{
    public function helper(): string
    {
        return 'shared';
    }
}
