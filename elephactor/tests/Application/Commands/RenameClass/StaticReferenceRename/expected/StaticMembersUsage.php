<?php

namespace VirtualTestNamespace\Usage\Members;

class StaticMembersUsage
{
    public function info(): string
    {
        return \VirtualTestNamespace\Utility\NewUtility::$state . \VirtualTestNamespace\Utility\NewUtility::VERSION;
    }
}
