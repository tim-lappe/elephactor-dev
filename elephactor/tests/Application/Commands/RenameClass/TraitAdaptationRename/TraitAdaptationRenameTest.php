<?php

declare(strict_types=1);

namespace TimLappe\ElephactorTests\Application\Commands\RenameClass\TraitAdaptationRename;

use TimLappe\Elephactor\Domain\Php\AST\Model\Value\Identifier;
use TimLappe\Elephactor\Domain\Php\Index\ClassIndex\Criteria\ClassNameCriteria;
use TimLappe\Elephactor\Domain\Php\Refactoring\Commands\ClassRename;
use TimLappe\ElephactorTests\Application\ElephactorTestCase;
use TimLappe\Elephactor\Domain\Workspace\Model\Filesystem\File;

final class TraitAdaptationRenameTest extends ElephactorTestCase
{
    private File $aliasConsumer;
    private File $precedenceConsumer;

    public function setUp(): void
    {
        parent::setUp();

        $this->mountFixtureTree(__DIR__ . '/fixtures');
        $this->aliasConsumer = $this->virtualFileUnderSource('Usage', 'AliasTraitConsumer.php');
        $this->precedenceConsumer = $this->virtualFileUnderSource('Usage', 'Precedence', 'PrecedenceTraitConsumer.php');

        $this->workspace->reloadIndices();
    }

    public function testRenamesTraitAliasAdaptation(): void
    {
        $this->renameTrait();

        $this->assertVirtualFileMatchesExpectedPath(
            $this->aliasConsumer,
            __DIR__ . '/expected/AliasTraitConsumer.php',
        );
    }

    public function testRenamesTraitPrecedenceAdaptation(): void
    {
        $this->renameTrait();

        $this->assertVirtualFileMatchesExpectedPath(
            $this->precedenceConsumer,
            __DIR__ . '/expected/PrecedenceTraitConsumer.php',
        );
    }

    private function renameTrait(): void
    {
        $class = $this->workspace->classLikeIndex()->find(new ClassNameCriteria(new Identifier('LegacyTrait')))
            ->first();
        if ($class === null) {
            self::fail('Class LegacyTrait not found in workspace');
        }

        $executor = $this->application->refactoringExecutor();
        $executor->handle(new ClassRename($class, new Identifier('ModernTrait')));
    }
}
