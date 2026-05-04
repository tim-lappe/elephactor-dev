<?php

declare(strict_types=1);

namespace TimLappe\ElephactorTests\Application\Commands\RenameClass\NamedTypeReferenceRename;

use TimLappe\Elephactor\Domain\Php\AST\Model\Value\Identifier;
use TimLappe\Elephactor\Domain\Php\Index\ClassIndex\Criteria\ClassNameCriteria;
use TimLappe\Elephactor\Domain\Php\Refactoring\Commands\ClassRename;
use TimLappe\ElephactorTests\Application\ElephactorTestCase;
use TimLappe\Elephactor\Domain\Workspace\Model\Filesystem\File;

final class NamedTypeReferenceRenameTest extends ElephactorTestCase
{
    private File $simpleTypeUsage;
    private File $qualifiedTypeUsage;

    public function setUp(): void
    {
        parent::setUp();

        $this->mountFixtureTree(__DIR__ . '/fixtures');
        $this->simpleTypeUsage = $this->virtualFileUnderSource('Usage', 'TypeHintedService.php');
        $this->qualifiedTypeUsage = $this->virtualFileUnderSource('Usage', 'Qualified', 'QualifiedTypeHintedService.php');

        $this->workspace->reloadIndices();
    }

    public function testRenamesNamedTypeHintsWithImports(): void
    {
        $this->renameDependency();

        $this->assertVirtualFileMatchesExpectedPath(
            $this->simpleTypeUsage,
            __DIR__ . '/expected/TypeHintedService.php',
        );
    }

    public function testRenamesFullyQualifiedNamedTypes(): void
    {
        $this->renameDependency();

        $this->assertVirtualFileMatchesExpectedPath(
            $this->qualifiedTypeUsage,
            __DIR__ . '/expected/QualifiedTypeHintedService.php',
        );
    }

    private function renameDependency(): void
    {
        $class = $this->workspace->classLikeIndex()->find(new ClassNameCriteria(new Identifier('OldDependency')))
            ->first();
        if ($class === null) {
            self::fail('Class OldDependency not found in workspace');
        }

        $executor = $this->application->refactoringExecutor();
        $executor->handle(new ClassRename($class, new Identifier('NewDependency')));
    }
}
