<?php

declare(strict_types=1);

namespace TimLappe\ElephactorTests\Application\Commands\RenameClass\ClassLikeRename;

use TimLappe\Elephactor\Domain\Php\AST\Model\Value\Identifier;
use TimLappe\Elephactor\Domain\Php\Index\ClassIndex\Criteria\ClassNameCriteria;
use TimLappe\Elephactor\Domain\Php\Refactoring\Commands\ClassRename;
use TimLappe\ElephactorTests\Application\ElephactorTestCase;
use TimLappe\Elephactor\Domain\Workspace\Model\Filesystem\File;

final class ClassLikeRenameTest extends ElephactorTestCase
{
    private File $interfaceFile;
    private File $traitFile;
    private File $enumFile;

    public function setUp(): void
    {
        parent::setUp();

        $this->mountFixtureTree(__DIR__ . '/fixtures');
        $this->interfaceFile = $this->virtualFileUnderSource('Contracts', 'LegacyInterface.php');
        $this->traitFile = $this->virtualFileUnderSource('Behavior', 'LegacyTrait.php');
        $this->enumFile = $this->virtualFileUnderSource('State', 'LegacyStatus.php');

        $this->workspace->reloadIndices();
    }

    public function testRenamesInterfaceDefinition(): void
    {
        $this->renameClassLike('LegacyInterface', 'RenamedInterface');

        self::assertEquals('RenamedInterface.php', $this->interfaceFile->name());
        $this->assertVirtualFileMatchesExpectedPath(
            $this->interfaceFile,
            __DIR__ . '/expected/RenamedInterface.php',
        );
    }

    public function testRenamesTraitDefinition(): void
    {
        $this->renameClassLike('LegacyTrait', 'RenamedTrait');

        self::assertEquals('RenamedTrait.php', $this->traitFile->name());
        $this->assertVirtualFileMatchesExpectedPath(
            $this->traitFile,
            __DIR__ . '/expected/RenamedTrait.php',
        );
    }

    public function testRenamesEnumDefinition(): void
    {
        $this->renameClassLike('LegacyStatus', 'RenamedStatus');

        self::assertEquals('RenamedStatus.php', $this->enumFile->name());
        $this->assertVirtualFileMatchesExpectedPath(
            $this->enumFile,
            __DIR__ . '/expected/RenamedStatus.php',
        );
    }

    private function renameClassLike(string $oldName, string $newName): void
    {
        $class = $this->workspace->classLikeIndex()->find(new ClassNameCriteria(new Identifier($oldName)))->first();
        if ($class === null) {
            self::fail(sprintf('Class like %s not found in workspace', $oldName));
        }

        $executor = $this->application->refactoringExecutor();
        $executor->handle(new ClassRename($class, new Identifier($newName)));
    }
}
