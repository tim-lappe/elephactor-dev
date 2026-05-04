<?php

declare(strict_types=1);

namespace TimLappe\ElephactorTests\Application\Commands\RenameClass\Php85SyntaxRename;

use TimLappe\Elephactor\Domain\Php\AST\Model\Value\Identifier;
use TimLappe\Elephactor\Domain\Php\Index\ClassIndex\Criteria\ClassNameCriteria;
use TimLappe\Elephactor\Domain\Php\Model\PhpVersion;
use TimLappe\Elephactor\Domain\Php\Refactoring\Commands\ClassRename;
use TimLappe\ElephactorTests\Application\ElephactorTestCase;
use TimLappe\Elephactor\Domain\Workspace\Model\Filesystem\File;

final class Php85SyntaxRenameTest extends ElephactorTestCase
{
    private File $pipeUsage;

    protected function phpVersion(): PhpVersion
    {
        return PhpVersion::PHP_8_5;
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->mountFixtureTree(__DIR__ . '/fixtures');
        $this->pipeUsage = $this->virtualFileUnderSource('Usage', 'PipeUsage.php');

        $this->workspace->reloadIndices();
    }

    public function testRenamesReferencesInPhp85Expressions(): void
    {
        $class = $this->workspace->classLikeIndex()->find(new ClassNameCriteria(new Identifier('OldTransformer')))
            ->first();
        if ($class === null) {
            self::fail('Class OldTransformer not found in workspace');
        }

        $executor = $this->application->refactoringExecutor();
        $executor->handle(new ClassRename($class, new Identifier('NewTransformer')));

        $this->assertVirtualFileMatchesExpectedPath(
            $this->pipeUsage,
            __DIR__ . '/expected/PipeUsage.php',
        );
    }
}
