<?php

namespace VirtualTestNamespace\Bar;

use VirtualTestNamespace\Foo\NewClass;

class SimpleImportClass
{
    public function reference(): string
    {
        return NewClass::class;
    }
}
