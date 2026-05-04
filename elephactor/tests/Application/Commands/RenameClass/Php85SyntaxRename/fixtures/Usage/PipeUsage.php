<?php

namespace VirtualTestNamespace\Usage;

use VirtualTestNamespace\Services\OldTransformer;

final class PipeUsage
{
    public function matches(string $message): bool
    {
        (void) $message;

        return $message
            |> strtolower(...)
            |> (static fn (string $normalized): bool => OldTransformer::accepts($normalized));
    }
}
