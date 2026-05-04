<?php

declare(strict_types=1);

namespace TimLappe\ElephactorTests\Application\Commands\RenameClass\InterfaceExtendsRename;

use TimLappe\Elephactor\Domain\Php\AST\Model\Value\Identifier;
use TimLappe\Elephactor\Domain\Php\Index\ClassIndex\Criteria\ClassNameCriteria;
use TimLappe\Elephactor\Domain\Php\Refactoring\Commands\ClassRename;
use TimLappe\ElephactorTests\Application\ElephactorTestCase;
use TimLappe\Elephactor\Domain\Workspace\Model\Filesystem\File;

final class InterfaceExtendsRenameTest extends ElephactorTestCase
{
    private File $childInterface;
    private File $multiInterface;

    public function setUp(): void
    {
        parent::setUp();

        $this->mountFixtureTree(__DIR__ . '/fixtures');
        $this->childInterface = $this->virtualFileUnderSource('Usage', 'ChildInterface.php');
        $this->multiInterface = $this->virtualFileUnderSource('Usage', 'Complex', 'MultiInterface.php');

        $this->workspace->reloadIndices();
    }

    public function testRenamesInterfaceExtendsClause(): void
    {
        $this->renameBaseInterface();

        $this->assertVirtualFileMatchesExpectedPath(
            $this->childInterface,
            __DIR__ . '/expected/ChildInterface.php',
        );
    }

    public function testRenamesInterfaceMultipleExtendsClause(): void
    {
        $this->renameBaseInterface();

        $this->assertVirtualFileMatchesExpectedPath(
            $this->multiInterface,
            __DIR__ . '/expected/MultiInterface.php',
        );
    }

    private function renameBaseInterface(): void
    {
        $class = $this->workspace->classLikeIndex()->find(new ClassNameCriteria(new Identifier('BaseInterface')))
            ->first();
        if ($class === null) {
            self::fail('Class BaseInterface not found in workspace');
        }

        $executor = $this->application->refactoringExecutor();
        $executor->handle(new ClassRename($class, new Identifier('RenamedBaseInterface')));
    }
}
