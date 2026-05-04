<?php

namespace VirtualTestNamespace\Usage\Members;

class StaticMembersUsage
{
    public function info(): string
    {
        return \VirtualTestNamespace\Utility\OldUtility::$state . \VirtualTestNamespace\Utility\OldUtility::VERSION;
    }
}
