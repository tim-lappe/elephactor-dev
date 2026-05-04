<?php

declare(strict_types=1);

namespace TimLappe\ElephactorTests\Application\Commands\RenameClass\NewExpressionRename;

use TimLappe\Elephactor\Domain\Php\AST\Model\Value\Identifier;
use TimLappe\Elephactor\Domain\Php\Index\ClassIndex\Criteria\ClassNameCriteria;
use TimLappe\Elephactor\Domain\Php\Refactoring\Commands\ClassRename;
use TimLappe\ElephactorTests\Application\ElephactorTestCase;
use TimLappe\Elephactor\Domain\Workspace\Model\Filesystem\File;

final class NewExpressionRenameTest extends ElephactorTestCase
{
    private File $simpleFactory;
    private File $qualifiedFactory;

    public function setUp(): void
    {
        parent::setUp();

        $this->mountFixtureTree(__DIR__ . '/fixtures');
        $this->simpleFactory = $this->virtualFileUnderSource('Factories', 'SimpleFactory.php');
        $this->qualifiedFactory = $this->virtualFileUnderSource('Factories', 'Advanced', 'QualifiedFactory.php');

        $this->workspace->reloadIndices();
    }

    public function testRenamesNewExpressionWithImport(): void
    {
        $this->renameService();

        $this->assertVirtualFileMatchesExpectedPath(
            $this->simpleFactory,
            __DIR__ . '/expected/SimpleFactory.php',
        );
    }

    public function testRenamesFullyQualifiedNewExpression(): void
    {
        $this->renameService();

        $this->assertVirtualFileMatchesExpectedPath(
            $this->qualifiedFactory,
            __DIR__ . '/expected/QualifiedFactory.php',
        );
    }

    private function renameService(): void
    {
        $class = $this->workspace->classLikeIndex()->find(new ClassNameCriteria(new Identifier('OldService')))
            ->first();
        if ($class === null) {
            self::fail('Class OldService not found in workspace');
        }

        $executor = $this->application->refactoringExecutor();
        $executor->handle(new ClassRename($class, new Identifier('NewService')));
    }
}
