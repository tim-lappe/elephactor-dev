<?php

declare(strict_types=1);

namespace TimLappe\ElephactorTests\Application\Commands\RenameClass\ClassExtendsRename;

use TimLappe\Elephactor\Domain\Php\AST\Model\Value\Identifier;
use TimLappe\Elephactor\Domain\Php\Index\ClassIndex\Criteria\ClassNameCriteria;
use TimLappe\Elephactor\Domain\Php\Refactoring\Commands\ClassRename;
use TimLappe\ElephactorTests\Application\ElephactorTestCase;
use TimLappe\Elephactor\Domain\Workspace\Model\Filesystem\File;

final class ClassExtendsRenameTest extends ElephactorTestCase
{
    private File $simpleChild;
    private File $qualifiedChild;

    public function setUp(): void
    {
        parent::setUp();

        $this->mountFixtureTree(__DIR__ . '/fixtures');
        $this->simpleChild = $this->virtualFileUnderSource('Usage', 'SimpleChild.php');
        $this->qualifiedChild = $this->virtualFileUnderSource('Usage', 'Deep', 'QualifiedChild.php');

        $this->workspace->reloadIndices();
    }

    public function testRenamesExtendsClauseWithImport(): void
    {
        $this->renameParent();

        $this->assertVirtualFileMatchesExpectedPath(
            $this->simpleChild,
            __DIR__ . '/expected/SimpleChild.php',
        );
    }

    public function testRenamesFullyQualifiedExtendsClause(): void
    {
        $this->renameParent();

        $this->assertVirtualFileMatchesExpectedPath(
            $this->qualifiedChild,
            __DIR__ . '/expected/QualifiedChild.php',
        );
    }

    private function renameParent(): void
    {
        $class = $this->workspace->classLikeIndex()->find(new ClassNameCriteria(new Identifier('ParentBase')))
            ->first();
        if ($class === null) {
            self::fail('Class ParentBase not found in workspace');
        }

        $executor = $this->application->refactoringExecutor();
        $executor->handle(new ClassRename($class, new Identifier('RenamedParentBase')));
    }
}
