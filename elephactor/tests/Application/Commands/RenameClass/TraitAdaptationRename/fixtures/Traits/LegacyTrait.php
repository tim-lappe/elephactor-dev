<?php

namespace VirtualTestNamespace\Traits;

trait LegacyTrait
{
    public function run(): string
    {
        return 'legacy';
    }

    public function conflict(): string
    {
        return 'legacy-conflict';
    }
}
