<?php

declare(strict_types=1);

namespace TimLappe\ElephactorTests\Application\Commands\RenameClass\AnonymousClassRename;

use TimLappe\Elephactor\Domain\Php\AST\Model\Value\Identifier;
use TimLappe\Elephactor\Domain\Php\Index\ClassIndex\Criteria\ClassNameCriteria;
use TimLappe\Elephactor\Domain\Php\Refactoring\Commands\ClassRename;
use TimLappe\ElephactorTests\Application\ElephactorTestCase;
use TimLappe\Elephactor\Domain\Workspace\Model\Filesystem\File;

final class AnonymousClassRenameTest extends ElephactorTestCase
{
    private File $anonymousExtendsUsage;
    private File $anonymousImplementsUsage;

    public function setUp(): void
    {
        parent::setUp();

        $this->mountFixtureTree(__DIR__ . '/fixtures');
        $this->anonymousExtendsUsage = $this->virtualFileUnderSource('AnonymousUsage', 'AnonymousFactory.php');
        $this->anonymousImplementsUsage = $this->virtualFileUnderSource('AnonymousUsage', 'Interfaces', 'InterfaceAnonymousFactory.php');

        $this->workspace->reloadIndices();
    }

    public function testRenamesAnonymousExtendsReference(): void
    {
        $this->renameTarget('OldAnonymousBase', 'NewAnonymousBase');

        $this->assertVirtualFileMatchesExpectedPath(
            $this->anonymousExtendsUsage,
            __DIR__ . '/expected/AnonymousFactory.php',
        );
    }

    public function testRenamesAnonymousImplementsReference(): void
    {
        $this->renameTarget('OldAnonymousInterface', 'NewAnonymousInterface');

        $this->assertVirtualFileMatchesExpectedPath(
            $this->anonymousImplementsUsage,
            __DIR__ . '/expected/InterfaceAnonymousFactory.php',
        );
    }

    private function renameTarget(string $className, string $newName): void
    {
        $class = $this->workspace->classLikeIndex()->find(new ClassNameCriteria(new Identifier($className)))->first();
        if ($class === null) {
            self::fail(sprintf('Class %s not found in workspace', $className));
        }

        $executor = $this->application->refactoringExecutor();
        $executor->handle(new ClassRename($class, new Identifier($newName)));
    }
}
