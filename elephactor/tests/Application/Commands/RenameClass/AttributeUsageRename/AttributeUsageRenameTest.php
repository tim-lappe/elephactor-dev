<?php

declare(strict_types=1);

namespace TimLappe\ElephactorTests\Application\Commands\RenameClass\AttributeUsageRename;

use TimLappe\Elephactor\Domain\Php\AST\Model\Value\Identifier;
use TimLappe\Elephactor\Domain\Php\Index\ClassIndex\Criteria\ClassNameCriteria;
use TimLappe\Elephactor\Domain\Php\Refactoring\Commands\ClassRename;
use TimLappe\ElephactorTests\Application\ElephactorTestCase;
use TimLappe\Elephactor\Domain\Workspace\Model\Filesystem\File;

final class AttributeUsageRenameTest extends ElephactorTestCase
{
    private File $simpleUsage;
    private File $qualifiedUsage;

    public function setUp(): void
    {
        parent::setUp();

        $this->mountFixtureTree(__DIR__ . '/fixtures');
        $this->simpleUsage = $this->virtualFileUnderSource('Usage', 'SimpleAttributeUsage.php');
        $this->qualifiedUsage = $this->virtualFileUnderSource('Usage', 'Qualified', 'QualifiedAttributeUsage.php');

        $this->workspace->reloadIndices();
    }

    public function testRenamesImportedAttributeUsage(): void
    {
        $this->renameAttribute();

        $this->assertVirtualFileMatchesExpectedPath(
            $this->simpleUsage,
            __DIR__ . '/expected/SimpleAttributeUsage.php',
        );
    }

    public function testRenamesFullyQualifiedAttributeUsage(): void
    {
        $this->renameAttribute();

        $this->assertVirtualFileMatchesExpectedPath(
            $this->qualifiedUsage,
            __DIR__ . '/expected/QualifiedAttributeUsage.php',
        );
    }

    private function renameAttribute(): void
    {
        $class = $this->workspace->classLikeIndex()->find(new ClassNameCriteria(new Identifier('OldAttribute')))
            ->first();

        if ($class === null) {
            self::fail('Class OldAttribute not found in workspace');
        }

        $executor = $this->application->refactoringExecutor();
        $executor->handle(new ClassRename($class, new Identifier('NewAttribute')));
    }
}
