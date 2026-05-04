<?php

namespace VirtualTestNamespace\Consumer;

use VirtualTestNamespace\Utility\Primary\PrimaryUtility;

final class DuplicateClassConsumer
{
    public function build(): PrimaryUtility
    {
        return new PrimaryUtility();
    }
}
