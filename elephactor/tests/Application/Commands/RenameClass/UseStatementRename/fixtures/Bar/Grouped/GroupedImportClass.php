<?php

namespace VirtualTestNamespace\Bar\Grouped;

use VirtualTestNamespace\Foo\{OldClass, HelperClass};

class GroupedImportClass
{
    public function createInstance(): string
    {
        return OldClass::class;
    }
}
