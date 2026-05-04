<?php

declare(strict_types=1);

namespace TimLappe\ElephactorTests\Application\Commands\RenameClass\StaticReferenceRename;

use TimLappe\Elephactor\Domain\Php\AST\Model\Value\Identifier;
use TimLappe\Elephactor\Domain\Php\Index\ClassIndex\Criteria\ClassNameCriteria;
use TimLappe\Elephactor\Domain\Php\Refactoring\Commands\ClassRename;
use TimLappe\ElephactorTests\Application\ElephactorTestCase;
use TimLappe\Elephactor\Domain\Workspace\Model\Filesystem\File;

final class StaticReferenceRenameTest extends ElephactorTestCase
{
    private File $methodUsage;
    private File $memberUsage;

    public function setUp(): void
    {
        parent::setUp();

        $this->mountFixtureTree(__DIR__ . '/fixtures');
        $this->methodUsage = $this->virtualFileUnderSource('Usage', 'StaticCallUsage.php');
        $this->memberUsage = $this->virtualFileUnderSource('Usage', 'Members', 'StaticMembersUsage.php');

        $this->workspace->reloadIndices();
    }

    public function testRenamesStaticMethodCalls(): void
    {
        $this->renameUtility();

        $this->assertVirtualFileMatchesExpectedPath(
            $this->methodUsage,
            __DIR__ . '/expected/StaticCallUsage.php',
        );
    }

    public function testRenamesStaticMembersAndConstants(): void
    {
        $this->renameUtility();

        $this->assertVirtualFileMatchesExpectedPath(
            $this->memberUsage,
            __DIR__ . '/expected/StaticMembersUsage.php',
        );
    }

    private function renameUtility(): void
    {
        $class = $this->workspace->classLikeIndex()->find(new ClassNameCriteria(new Identifier('OldUtility')))
            ->first();
        if ($class === null) {
            self::fail('Class OldUtility not found in workspace');
        }

        $executor = $this->application->refactoringExecutor();
        $executor->handle(new ClassRename($class, new Identifier('NewUtility')));
    }
}
