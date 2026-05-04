<?php

declare(strict_types=1);

namespace TimLappe\ElephactorTests\Application\Commands\MoveClass\BasicClassMoving;

use TimLappe\Elephactor\Domain\Php\AST\Model\Value\Identifier;
use TimLappe\Elephactor\Domain\Php\Index\ClassIndex\Criteria\ClassNameCriteria;
use TimLappe\Elephactor\Domain\Php\Refactoring\Commands\MoveFile;
use TimLappe\Elephactor\Domain\Psr4\Model\Psr4ClassFile;
use TimLappe\Elephactor\Domain\Workspace\Model\Filesystem\File;
use TimLappe\ElephactorTests\Application\ElephactorTestCase;
use TimLappe\ElephactorTests\Application\VirtualDirectory;

final class BasicClassMovingTest extends ElephactorTestCase
{
    private VirtualDirectory $targetDirectory;

    public function setUp(): void
    {
        parent::setUp();

        $this->mountFixtureTree(__DIR__ . '/fixtures');
        $this->targetDirectory = $this->sourceDirectory->createOrGetDirecotry('NewDirectory');

        $this->workspace->reloadIndices();
    }

    public function testBasicClassMoving(): void
    {
        $class = $this->workspace->classLikeIndex()->find(new ClassNameCriteria(new Identifier('FooClass')))->first();
        if ($class === null) {
            self::fail('Class FooClass not found in workspace.');
        }

        if (!$class instanceof Psr4ClassFile) {
            self::fail('Class FooClass is not a Psr4ClassFile');
        }

        $this->application
            ->refactoringExecutor()
            ->handle(new MoveFile($class->file(), $this->targetDirectory));

        $movedFile = $this->targetDirectory
            ->childFiles()
            ->first(fn (File $file): bool => $file->name() === 'FooClass.php');

        self::assertNotNull($movedFile, 'Moved file not found in target directory');

        $this->assertVirtualFileMatchesExpectedPath(
            $movedFile,
            __DIR__ . '/expected/NewDirectory/FooClass.php',
        );
    }

    public function testCanSkipFileMove(): void
    {
        $class = $this->workspace->classLikeIndex()->find(new ClassNameCriteria(new Identifier('FooClass')))->first();
        if ($class === null) {
            self::fail('Class FooClass not found in workspace.');
        }

        if (!$class instanceof Psr4ClassFile) {
            self::fail('Class FooClass is not a Psr4ClassFile');
        }

        $this->application
            ->refactoringExecutor()
            ->handle(new MoveFile($class->file(), $this->targetDirectory, false));

        $movedFile = $this->targetDirectory
            ->childFiles()
            ->first(fn (File $file): bool => $file->name() === 'FooClass.php');

        self::assertNull($movedFile, 'File should not be moved to target directory');
        $this->assertVirtualFileMatchesExpectedPath(
            $class->file()->handle(),
            __DIR__ . '/expected/NewDirectory/FooClass.php',
        );
    }
}
