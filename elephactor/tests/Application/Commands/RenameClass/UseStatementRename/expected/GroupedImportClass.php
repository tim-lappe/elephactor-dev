<?php

namespace VirtualTestNamespace\Bar\Grouped;

use VirtualTestNamespace\Foo\{NewClass, HelperClass};

class GroupedImportClass
{
    public function createInstance(): string
    {
        return NewClass::class;
    }
}
