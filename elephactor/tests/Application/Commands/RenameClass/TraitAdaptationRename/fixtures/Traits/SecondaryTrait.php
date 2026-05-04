<?php

namespace VirtualTestNamespace\Traits;

trait SecondaryTrait
{
    public function conflict(): string
    {
        return 'secondary';
    }
}
