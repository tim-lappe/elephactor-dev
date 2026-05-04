<?php

declare(strict_types=1);

namespace TimLappe\ElephactorTests\Application\Commands\RenameClass\FullyQualifiedSelectionRename;

use TimLappe\Elephactor\Domain\Php\AST\Model\Value\Identifier;
use TimLappe\Elephactor\Domain\Php\AST\Model\Value\QualifiedName;
use TimLappe\Elephactor\Domain\Php\Index\ClassIndex\Criteria\ClassNameCriteria;
use TimLappe\Elephactor\Domain\Php\Refactoring\Commands\ClassRename;
use TimLappe\ElephactorTests\Application\ElephactorTestCase;
use TimLappe\Elephactor\Domain\Workspace\Model\Filesystem\File;

final class FullyQualifiedSelectionRenameTest extends ElephactorTestCase
{
    private File $primaryDuplicate;
    private File $secondaryDuplicate;
    private File $usage;

    public function setUp(): void
    {
        parent::setUp();

        $this->mountFixtureTree(__DIR__ . '/fixtures');
        $this->primaryDuplicate = $this->virtualFileUnderSource('Utility', 'Primary', 'DuplicateClass.php');
        $this->secondaryDuplicate = $this->virtualFileUnderSource('Utility', 'Secondary', 'DuplicateClass.php');
        $this->usage = $this->virtualFileUnderSource('Consumer', 'DuplicateClassConsumer.php');

        $this->workspace->reloadIndices();
    }

    public function testRenamesOnlySpecifiedFullyQualifiedClass(): void
    {
        $class = $this->workspace->classLikeIndex()->find(new ClassNameCriteria(QualifiedName::fromString('VirtualTestNamespace\Utility\Primary\DuplicateClass')))
            ->first();

        if ($class === null) {
            self::fail('Class VirtualTestNamespace\Utility\Primary\DuplicateClass not found in workspace');
        }

        $executor = $this->application->refactoringExecutor();
        $executor->handle(new ClassRename($class, new Identifier('PrimaryUtility')));

        $this->assertVirtualFileMatchesExpectedPath(
            $this->primaryDuplicate,
            __DIR__ . '/expected/PrimaryUtility.php',
        );

        $this->assertVirtualFileMatchesExpectedPath(
            $this->secondaryDuplicate,
            __DIR__ . '/expected/SecondaryDuplicateClass.php',
        );

        $this->assertVirtualFileMatchesExpectedPath(
            $this->usage,
            __DIR__ . '/expected/DuplicateClassConsumer.php',
        );
    }
}
