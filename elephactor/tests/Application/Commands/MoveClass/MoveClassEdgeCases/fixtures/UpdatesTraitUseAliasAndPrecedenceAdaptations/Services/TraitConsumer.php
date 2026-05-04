<?php

namespace VirtualTestNamespace\Services;

use VirtualTestNamespace\Behavior\{CompetingTrait, SharedTrait};

final class TraitConsumer
{
    use SharedTrait;

    use SharedTrait {
        SharedTrait::helper as sharedAlias;
    }

    use SharedTrait, CompetingTrait {
        SharedTrait::helper insteadof CompetingTrait;
        CompetingTrait::helper as competingAlias;
    }

    use CompetingTrait, SharedTrait {
        CompetingTrait::helper insteadof SharedTrait;
    }
}
