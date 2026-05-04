<?php

namespace VirtualTestNamespace\Consumers;

use VirtualTestNamespace\Domain\{HelperClass, TargetClass};
use VirtualTestNamespace\Domain\TargetClass as ImportedAlias;

#[TargetClass]
final class ComplexUsage extends TargetClass
{
    public function __construct(private TargetClass $typedProperty, private HelperClass|TargetClass $union)
    {
    }

    public function aliasUsage(): ImportedAlias
    {
        return new ImportedAlias();
    }

    public function build(TargetClass $parameter): TargetClass
    {
        $instance = new TargetClass();
        TargetClass::create();
        TargetClass::$counter++;
        $value = TargetClass::CONSTANT;

        if ($instance instanceof TargetClass) {
            return $instance;
        }

        \VirtualTestNamespace\Domain\TargetClass::create();

        return new class($parameter) extends TargetClass {
            public function __construct(private TargetClass $inner)
            {
            }

            public function descriptor(): string
            {
                return TargetClass::class;
            }
        };
    }
}
