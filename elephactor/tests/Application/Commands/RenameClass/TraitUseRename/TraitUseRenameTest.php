<?php

declare(strict_types=1);

namespace TimLappe\ElephactorTests\Application\Commands\RenameClass\TraitUseRename;

use TimLappe\Elephactor\Domain\Php\AST\Model\Value\Identifier;
use TimLappe\Elephactor\Domain\Php\Index\ClassIndex\Criteria\ClassNameCriteria;
use TimLappe\Elephactor\Domain\Php\Refactoring\Commands\ClassRename;
use TimLappe\ElephactorTests\Application\ElephactorTestCase;
use TimLappe\Elephactor\Domain\Workspace\Model\Filesystem\File;

final class TraitUseRenameTest extends ElephactorTestCase
{
    private File $simpleUsage;
    private File $qualifiedUsage;

    public function setUp(): void
    {
        parent::setUp();

        $this->mountFixtureTree(__DIR__ . '/fixtures');
        $this->simpleUsage = $this->virtualFileUnderSource('Usage', 'SimpleTraitConsumer.php');
        $this->qualifiedUsage = $this->virtualFileUnderSource('Usage', 'Qualified', 'QualifiedTraitConsumer.php');

        $this->workspace->reloadIndices();
    }

    public function testRenamesTraitUseWithImport(): void
    {
        $this->renameTrait();

        $this->assertVirtualFileMatchesExpectedPath(
            $this->simpleUsage,
            __DIR__ . '/expected/SimpleTraitConsumer.php',
        );
    }

    public function testRenamesFullyQualifiedTraitUse(): void
    {
        $this->renameTrait();

        $this->assertVirtualFileMatchesExpectedPath(
            $this->qualifiedUsage,
            __DIR__ . '/expected/QualifiedTraitConsumer.php',
        );
    }

    private function renameTrait(): void
    {
        $class = $this->workspace->classLikeIndex()->find(new ClassNameCriteria(new Identifier('OldTrait')))
            ->first();
        if ($class === null) {
            self::fail('Class OldTrait not found in workspace');
        }

        $executor = $this->application->refactoringExecutor();
        $executor->handle(new ClassRename($class, new Identifier('NewTrait')));
    }
}
