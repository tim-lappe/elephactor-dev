<?php

declare(strict_types=1);

namespace TimLappe\ElephactorTests\Application\Commands\RenameClass\BasicClassRename;

use TimLappe\Elephactor\Domain\Php\AST\Model\Value\Identifier;
use TimLappe\Elephactor\Domain\Php\Index\ClassIndex\Criteria\ClassNameCriteria;
use TimLappe\Elephactor\Domain\Php\Refactoring\Commands\ClassRename;
use TimLappe\ElephactorTests\Application\ElephactorTestCase;
use TimLappe\Elephactor\Domain\Workspace\Model\Filesystem\File;

class BasicClassRenameTest extends ElephactorTestCase
{
    private File $oldClass;

    public function setUp(): void
    {
        parent::setUp();

        $this->mountFixtureTree(__DIR__ . '/fixtures');
        $this->oldClass = $this->virtualFileUnderSource('OldClass.php');

        $this->workspace->reloadIndices();
    }

    public function testBasicClassRename(): void
    {
        $this->renameClass('OldClass', 'NewClass');

        self::assertEquals('NewClass.php', $this->oldClass->name());
        $this->assertVirtualFileMatchesExpectedPath($this->oldClass, __DIR__ . '/expected/NewClass.php');
    }

    public function testCanSkipFileRename(): void
    {
        $class = $this->workspace->classLikeIndex()->find(new ClassNameCriteria(new Identifier('OldClass')))->first();
        if ($class === null) {
            self::fail('Class OldClass not found in workspace');
        }

        $this->application
            ->refactoringExecutor()
            ->handle(new ClassRename($class, new Identifier('NewClass'), false));

        self::assertEquals('OldClass.php', $this->oldClass->name());
        $this->assertVirtualFileMatchesExpectedPath($this->oldClass, __DIR__ . '/expected/NewClass.php');
    }

    private function renameClass(string $oldName, string $newName): void
    {
        $class = $this->workspace->classLikeIndex()->find(new ClassNameCriteria(new Identifier($oldName)))->first();
        if ($class === null) {
            self::fail(sprintf('Class %s not found in workspace', $oldName));
        }

        $executor = $this->application->refactoringExecutor();
        $executor->handle(new ClassRename($class, new Identifier($newName)));
    }
}
