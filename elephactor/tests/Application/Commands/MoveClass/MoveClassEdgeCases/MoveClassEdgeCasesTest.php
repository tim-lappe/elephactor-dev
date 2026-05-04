<?php

declare(strict_types=1);

namespace TimLappe\ElephactorTests\Application\Commands\MoveClass\MoveClassEdgeCases;

use TimLappe\Elephactor\Domain\Php\AST\Model\Value\Identifier;
use TimLappe\Elephactor\Domain\Php\Index\ClassIndex\Criteria\ClassNameCriteria;
use TimLappe\Elephactor\Domain\Php\Refactoring\Commands\MoveFile;
use TimLappe\Elephactor\Domain\Psr4\Model\Psr4ClassFile;
use TimLappe\ElephactorTests\Application\ElephactorTestCase;
use TimLappe\ElephactorTests\Application\VirtualDirectory;
use TimLappe\Elephactor\Domain\Workspace\Model\Filesystem\File;

final class MoveClassEdgeCasesTest extends ElephactorTestCase
{
    public function testUpdatesComplexClassReferencesAcrossExpressionsAndTypes(): void
    {
        $this->mountFixtureTree(__DIR__ . '/fixtures/UpdatesComplexClassReferencesAcrossExpressionsAndTypes');

        $targetDirectory = $this->sourceDirectory
            ->createOrGetDirecotry('Refactored')
            ->createOrGetDirecotry('Core');

        $this->workspace->reloadIndices();

        $this->moveClass('TargetClass', $targetDirectory);

        $movedFile = $this->findFileIn($targetDirectory, 'TargetClass.php');
        self::assertNotNull($movedFile);

        $this->assertVirtualFileMatchesExpectedPath(
            $movedFile,
            __DIR__ . '/expected/UpdatesComplexClassReferencesAcrossExpressionsAndTypes/TargetClass.php',
        );

        $consumersDir = $this->sourceDirectory->createOrGetDirecotry('Consumers');
        $complexUsage = $this->findFileIn($consumersDir, 'ComplexUsage.php');
        self::assertNotNull($complexUsage);

        $this->assertVirtualFileMatchesExpectedPath(
            $complexUsage,
            __DIR__ . '/expected/UpdatesComplexClassReferencesAcrossExpressionsAndTypes/ComplexUsage.php',
        );
    }

    public function testUpdatesInterfacesAcrossImplementationsAnonymousClassesAndEnums(): void
    {
        $this->mountFixtureTree(__DIR__ . '/fixtures/UpdatesInterfacesAcrossImplementationsAnonymousClassesAndEnums');

        $targetDirectory = $this->sourceDirectory->createOrGetDirecotry('Protocols');

        $this->workspace->reloadIndices();

        $this->moveClass('FooContract', $targetDirectory);

        $movedFile = $this->findFileIn($targetDirectory, 'FooContract.php');
        self::assertNotNull($movedFile);

        $this->assertVirtualFileMatchesExpectedPath(
            $movedFile,
            __DIR__ . '/expected/UpdatesInterfacesAcrossImplementationsAnonymousClassesAndEnums/FooContract.php',
        );

        $extensionsDir = $this->sourceDirectory->createOrGetDirecotry('Extensions');
        $childContract = $this->findFileIn($extensionsDir, 'ChildContract.php');
        self::assertNotNull($childContract);
        $this->assertVirtualFileMatchesExpectedPath(
            $childContract,
            __DIR__ . '/expected/UpdatesInterfacesAcrossImplementationsAnonymousClassesAndEnums/ChildContract.php',
        );

        $servicesDir = $this->sourceDirectory->createOrGetDirecotry('Services');
        $implementsContract = $this->findFileIn($servicesDir, 'ImplementsContract.php');
        self::assertNotNull($implementsContract);
        $this->assertVirtualFileMatchesExpectedPath(
            $implementsContract,
            __DIR__ . '/expected/UpdatesInterfacesAcrossImplementationsAnonymousClassesAndEnums/ImplementsContract.php',
        );

        $processorFile = $this->findFileIn($servicesDir, 'Processor.php');
        self::assertNotNull($processorFile);
        $this->assertVirtualFileMatchesExpectedPath(
            $processorFile,
            __DIR__ . '/expected/UpdatesInterfacesAcrossImplementationsAnonymousClassesAndEnums/Processor.php',
        );

        $stateDir = $this->sourceDirectory->createOrGetDirecotry('State');
        $enumFile = $this->findFileIn($stateDir, 'WorkflowStatus.php');
        self::assertNotNull($enumFile);
        $this->assertVirtualFileMatchesExpectedPath(
            $enumFile,
            __DIR__ . '/expected/UpdatesInterfacesAcrossImplementationsAnonymousClassesAndEnums/WorkflowStatus.php',
        );
    }

    public function testUpdatesTraitUseAliasAndPrecedenceAdaptations(): void
    {
        $this->mountFixtureTree(__DIR__ . '/fixtures/UpdatesTraitUseAliasAndPrecedenceAdaptations');

        $targetDirectory = $this->sourceDirectory
            ->createOrGetDirecotry('Refactored')
            ->createOrGetDirecotry('Mixins');

        $this->workspace->reloadIndices();

        $this->moveClass('SharedTrait', $targetDirectory);

        $movedFile = $this->findFileIn($targetDirectory, 'SharedTrait.php');
        self::assertNotNull($movedFile);

        $this->assertVirtualFileMatchesExpectedPath(
            $movedFile,
            __DIR__ . '/expected/UpdatesTraitUseAliasAndPrecedenceAdaptations/SharedTrait.php',
        );

        $servicesDir = $this->sourceDirectory->createOrGetDirecotry('Services');
        $consumerFile = $this->findFileIn($servicesDir, 'TraitConsumer.php');
        self::assertNotNull($consumerFile);

        $this->assertVirtualFileMatchesExpectedPath(
            $consumerFile,
            __DIR__ . '/expected/UpdatesTraitUseAliasAndPrecedenceAdaptations/TraitConsumer.php',
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
