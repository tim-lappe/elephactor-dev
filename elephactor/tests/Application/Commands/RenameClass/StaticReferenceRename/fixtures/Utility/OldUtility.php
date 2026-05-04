<?php

namespace VirtualTestNamespace\Utility;

class OldUtility
{
    public const VERSION = '1.0';
    public static string $state = 'idle';

    public static function perform(): string
    {
        return 'performing';
    }
}
