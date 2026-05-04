<?php

declare(strict_types=1);

namespace TimLappe\ElephactorTests\Application\Commands\MoveClass\MoveClassDependencyImport;

use TimLappe\Elephactor\Domain\Php\AST\Model\Value\Identifier;
use TimLappe\Elephactor\Domain\Php\Index\ClassIndex\Criteria\ClassNameCriteria;
use TimLappe\Elephactor\Domain\Php\Refactoring\Commands\MoveFile;
use TimLappe\Elephactor\Domain\Psr4\Model\Psr4ClassFile;
use TimLappe\Elephactor\Domain\Workspace\Model\Filesystem\File;
use TimLappe\ElephactorTests\Application\ElephactorTestCase;
use TimLappe\ElephactorTests\Application\VirtualDirectory;

final class MoveClassDependencyImportTest extends ElephactorTestCase
{
    public function testKeepsImportedDependenciesAndGlobalFunctionsWhenMoved(): void
    {
        $this->mountFixtureTree(__DIR__ . '/fixtures/KeepsImportedDependenciesAndGlobalFunctionsWhenMoved');

        $targetNamespace = $this->sourceDirectory
            ->createOrGetDirecotry('Printer')
            ->createOrGetDirecotry('Application')
            ->createOrGetDirecotry('Dto');

        $this->workspace->reloadIndices();

        $this->moveClass('MovedApplicationService', $targetNamespace);

        $movedFile = $this->findFileIn($targetNamespace, 'MovedApplicationService.php');
        self::assertNotNull($movedFile);

        $expected = file_get_contents(__DIR__ . '/expected/KeepsImportedDependenciesAndGlobalFunctionsWhenMoved/MovedApplicationService.php');
        self::assertNotFalse($expected);
        self::assertSame($expected, $movedFile->content());
    }

    public function testKeepsReferencesToSameNamespaceDependencies(): void
    {
        $this->mountFixtureTree(__DIR__ . '/fixtures/KeepsReferencesToSameNamespaceDependencies');

        $targetNamespace = $this->sourceDirectory
            ->createOrGetDirecotry('Domain')
            ->createOrGetDirecotry('Target');

        $this->workspace->reloadIndices();

        $this->moveClass('MovedClass', $targetNamespace);

        $movedFile = $this->findFileIn($targetNamespace, 'MovedClass.php');
        self::assertNotNull($movedFile);

        $this->assertVirtualFileMatchesExpectedPath(
            $movedFile,
            __DIR__ . '/expected/KeepsReferencesToSameNamespaceDependencies/MovedClass.php',
        );
    }

    public function testKeepsAliasedNamespaceAttributeReferencesWhenMoved(): void
    {
        $this->mountFixtureTree(__DIR__ . '/fixtures/KeepsAliasedNamespaceAttributeReferencesWhenMoved');

        $entityNamespace = $this->sourceDirectory
            ->createOrGetDirecotry('Catalog')
            ->createOrGetDirecotry('Domain')
            ->createOrGetDirecotry('Entity');

        $catalogItemImageFile = $entityNamespace
            ->childFiles()
            ->first(static fn (File $file): bool => $file->name() === 'CatalogItemImage.php');
        self::assertNotNull($catalogItemImageFile);

        $targetNamespace = $this->sourceDirectory
            ->createOrGetDirecotry('Catalog')
            ->createOrGetDirecotry('Application')
            ->createOrGetDirecotry('Controller');

        $this->workspace->reloadIndices();

        $this->moveClass('CatalogItem', $targetNamespace);

        $movedFile = $this->findFileIn($targetNamespace, 'CatalogItem.php');
        self::assertNotNull($movedFile);

        $this->assertVirtualFileMatchesExpectedPath(
            $movedFile,
            __DIR__ . '/expected/KeepsAliasedNamespaceAttributeReferencesWhenMoved/CatalogItem.php',
        );

        $this->assertVirtualFileMatchesExpectedPath(
            $catalogItemImageFile,
            __DIR__ . '/expected/KeepsAliasedNamespaceAttributeReferencesWhenMoved/CatalogItemImage.php',
        );
    }

    private function moveClass(string $className, VirtualDirectory $targetDirectory): void
    {
        $class = $this->workspace->classLikeIndex()->find(new ClassNameCriteria(new Identifier($className)))->first();
        if ($class === null) {
            self::fail(sprintf('Class %s not found in workspace', $className));
        }

        if (!$class instanceof Psr4ClassFile) {
            self::fail(sprintf('Class %s is not a Psr4ClassFile', $className));
        }

        $this->application
            ->refactoringExecutor()
            ->handle(new MoveFile($class->file(), $targetDirectory));
    }

    private function findFileIn(VirtualDirectory $directory, string $fileName): ?File
    {
        return $directory
            ->childFiles()
            ->first(static fn (File $file): bool => $file->name() === $fileName);
    }
}
