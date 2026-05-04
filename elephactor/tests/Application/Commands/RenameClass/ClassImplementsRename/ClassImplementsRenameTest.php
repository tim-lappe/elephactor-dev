<?php

declare(strict_types=1);

namespace TimLappe\ElephactorTests\Application\Commands\RenameClass\ClassImplementsRename;

use TimLappe\Elephactor\Domain\Php\AST\Model\Value\Identifier;
use TimLappe\Elephactor\Domain\Php\Index\ClassIndex\Criteria\ClassNameCriteria;
use TimLappe\Elephactor\Domain\Php\Refactoring\Commands\ClassRename;
use TimLappe\ElephactorTests\Application\ElephactorTestCase;
use TimLappe\Elephactor\Domain\Workspace\Model\Filesystem\File;

final class ClassImplementsRenameTest extends ElephactorTestCase
{
    private File $simpleImplementation;
    private File $complexImplementation;

    public function setUp(): void
    {
        parent::setUp();

        $this->mountFixtureTree(__DIR__ . '/fixtures');
        $this->simpleImplementation = $this->virtualFileUnderSource('Usage', 'SimpleImplementation.php');
        $this->complexImplementation = $this->virtualFileUnderSource('Usage', 'Complex', 'ComplexImplementation.php');

        $this->workspace->reloadIndices();
    }

    public function testRenamesImplementsClauseWithImport(): void
    {
        $this->renameContract();

        $this->assertVirtualFileMatchesExpectedPath(
            $this->simpleImplementation,
            __DIR__ . '/expected/SimpleImplementation.php',
        );
    }

    public function testRenamesFullyQualifiedImplementsClause(): void
    {
        $this->renameContract();

        $this->assertVirtualFileMatchesExpectedPath(
            $this->complexImplementation,
            __DIR__ . '/expected/ComplexImplementation.php',
        );
    }

    private function renameContract(): void
    {
        $class = $this->workspace->classLikeIndex()->find(new ClassNameCriteria(new Identifier('OldContract')))
            ->first();
        if ($class === null) {
            self::fail('Class OldContract not found in workspace');
        }

        $executor = $this->application->refactoringExecutor();
        $executor->handle(new ClassRename($class, new Identifier('NewContract')));
    }
}
