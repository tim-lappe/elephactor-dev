<?php

namespace VirtualTestNamespace\Services;

final class OldTransformer
{
    public static function accepts(string $message): bool
    {
        return $message !== '';
    }
}
