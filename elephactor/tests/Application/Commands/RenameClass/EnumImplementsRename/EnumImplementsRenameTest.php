<?php

declare(strict_types=1);

namespace TimLappe\ElephactorTests\Application\Commands\RenameClass\EnumImplementsRename;

use TimLappe\Elephactor\Domain\Php\AST\Model\Value\Identifier;
use TimLappe\Elephactor\Domain\Php\Index\ClassIndex\Criteria\ClassNameCriteria;
use TimLappe\Elephactor\Domain\Php\Refactoring\Commands\ClassRename;
use TimLappe\ElephactorTests\Application\ElephactorTestCase;
use TimLappe\Elephactor\Domain\Workspace\Model\Filesystem\File;

final class EnumImplementsRenameTest extends ElephactorTestCase
{
    private File $simpleEnum;
    private File $advancedEnum;

    public function setUp(): void
    {
        parent::setUp();

        $this->mountFixtureTree(__DIR__ . '/fixtures');
        $this->simpleEnum = $this->virtualFileUnderSource('Enums', 'SimpleEnum.php');
        $this->advancedEnum = $this->virtualFileUnderSource('Enums', 'Advanced', 'AdvancedEnum.php');

        $this->workspace->reloadIndices();
    }

    public function testRenamesEnumImplementsClause(): void
    {
        $this->renameContract();

        $this->assertVirtualFileMatchesExpectedPath(
            $this->simpleEnum,
            __DIR__ . '/expected/SimpleEnum.php',
        );
    }

    public function testRenamesFullyQualifiedEnumImplementsClause(): void
    {
        $this->renameContract();

        $this->assertVirtualFileMatchesExpectedPath(
            $this->advancedEnum,
            __DIR__ . '/expected/AdvancedEnum.php',
        );
    }

    private function renameContract(): void
    {
        $class = $this->workspace->classLikeIndex()->find(new ClassNameCriteria(new Identifier('BehaviorContract')))
            ->first();
        if ($class === null) {
            self::fail('Class BehaviorContract not found in workspace');
        }

        $executor = $this->application->refactoringExecutor();
        $executor->handle(new ClassRename($class, new Identifier('UpdatedBehaviorContract')));
    }
}
