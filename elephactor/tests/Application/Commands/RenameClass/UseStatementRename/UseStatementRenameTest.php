<?php

declare(strict_types=1);

namespace TimLappe\ElephactorTests\Application\Commands\RenameClass\UseStatementRename;

use TimLappe\Elephactor\Domain\Php\AST\Model\Value\Identifier;
use TimLappe\Elephactor\Domain\Php\Index\ClassIndex\Criteria\ClassNameCriteria;
use TimLappe\Elephactor\Domain\Php\Refactoring\Commands\ClassRename;
use TimLappe\ElephactorTests\Application\ElephactorTestCase;
use TimLappe\Elephactor\Domain\Workspace\Model\Filesystem\File;

final class UseStatementRenameTest extends ElephactorTestCase
{
    private File $simpleImportClass;
    private File $groupedImportClass;

    public function setUp(): void
    {
        parent::setUp();

        $this->mountFixtureTree(__DIR__ . '/fixtures');
        $this->simpleImportClass = $this->virtualFileUnderSource('Bar', 'SimpleImportClass.php');
        $this->groupedImportClass = $this->virtualFileUnderSource('Bar', 'Grouped', 'GroupedImportClass.php');

        $this->workspace->reloadIndices();
    }

    public function testRenamesSimpleUseStatement(): void
    {
        $this->renameTargetClass();

        $this->assertVirtualFileMatchesExpectedPath(
            $this->simpleImportClass,
            __DIR__ . '/expected/SimpleImportClass.php',
        );
    }

    public function testRenamesGroupedUseStatement(): void
    {
        $this->renameTargetClass();

        $this->assertVirtualFileMatchesExpectedPath(
            $this->groupedImportClass,
            __DIR__ . '/expected/GroupedImportClass.php',
        );
    }

    private function renameTargetClass(): void
    {
        $class = $this->workspace->classLikeIndex()->find(new ClassNameCriteria(new Identifier('OldClass')))
            ->first();
        if ($class === null) {
            self::fail('Class OldClass not found in workspace');
        }

        $executor = $this->application->refactoringExecutor();
        $executor->handle(new ClassRename($class, new Identifier('NewClass')));
    }
}
