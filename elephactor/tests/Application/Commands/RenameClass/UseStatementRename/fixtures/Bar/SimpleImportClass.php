<?php

namespace VirtualTestNamespace\Bar;

use VirtualTestNamespace\Foo\OldClass;

class SimpleImportClass
{
    public function reference(): string
    {
        return OldClass::class;
    }
}
