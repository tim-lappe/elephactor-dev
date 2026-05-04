<?php

declare(strict_types=1);

namespace TimLappe\ElephactorTests\Application\Commands\RenameClass\InstanceofExpressionRename;

use TimLappe\Elephactor\Domain\Php\AST\Model\Value\Identifier;
use TimLappe\Elephactor\Domain\Php\Index\ClassIndex\Criteria\ClassNameCriteria;
use TimLappe\Elephactor\Domain\Php\Refactoring\Commands\ClassRename;
use TimLappe\ElephactorTests\Application\ElephactorTestCase;
use TimLappe\Elephactor\Domain\Workspace\Model\Filesystem\File;

final class InstanceofExpressionRenameTest extends ElephactorTestCase
{
    private File $simpleChecker;
    private File $qualifiedChecker;

    public function setUp(): void
    {
        parent::setUp();

        $this->mountFixtureTree(__DIR__ . '/fixtures');
        $this->simpleChecker = $this->virtualFileUnderSource('Usage', 'InstanceofChecker.php');
        $this->qualifiedChecker = $this->virtualFileUnderSource('Usage', 'Advanced', 'QualifiedInstanceofChecker.php');

        $this->workspace->reloadIndices();
    }

    public function testRenamesInstanceofWithImport(): void
    {
        $this->renameType();

        $this->assertVirtualFileMatchesExpectedPath(
            $this->simpleChecker,
            __DIR__ . '/expected/InstanceofChecker.php',
        );
    }

    public function testRenamesFullyQualifiedInstanceof(): void
    {
        $this->renameType();

        $this->assertVirtualFileMatchesExpectedPath(
            $this->qualifiedChecker,
            __DIR__ . '/expected/QualifiedInstanceofChecker.php',
        );
    }

    private function renameType(): void
    {
        $class = $this->workspace->classLikeIndex()->find(new ClassNameCriteria(new Identifier('OldType')))
            ->first();
        if ($class === null) {
            self::fail('Class OldType not found in workspace');
        }

        $executor = $this->application->refactoringExecutor();
        $executor->handle(new ClassRename($class, new Identifier('NewType')));
    }
}
