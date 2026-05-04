<?php

namespace VirtualTestNamespace\Behavior;

trait CompetingTrait
{
    public function helper(): string
    {
        return 'competing';
    }
}
