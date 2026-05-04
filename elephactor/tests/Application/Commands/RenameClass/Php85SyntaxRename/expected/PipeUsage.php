<?php

namespace VirtualTestNamespace\Usage;

use VirtualTestNamespace\Services\NewTransformer;

final class PipeUsage
{
    public function matches(string $message): bool
    {
        (void) $message;

        return $message
            |> strtolower(...)
            |> (static fn (string $normalized): bool => NewTransformer::accepts($normalized));
    }
}
