<?php

declare(strict_types=1);

namespace TimLappe\ElephactorTests\Application;

use PHPUnit\Framework\TestCase;
use TimLappe\Elephactor\Adapter\Php\Ast\Nikic\Builder\NikicToDomain\NikicToDomainNodeMapper;
use TimLappe\Elephactor\Adapter\Php\Ast\Nikic\Loader\NikicAstBuilder;
use TimLappe\Elephactor\Application;
use TimLappe\Elephactor\Domain\Php\AST\Model\Value\QualifiedName;
use TimLappe\Elephactor\Domain\Php\AST\Model\Value\Identifier;
use TimLappe\Elephactor\Domain\Workspace\Model\Environment;
use TimLappe\Elephactor\Domain\Php\Model\PhpVersion;
use TimLappe\Elephactor\Domain\Php\Repository\PhpFileRepository;
use TimLappe\Elephactor\Domain\Psr4\Adapter\Index\Psr4PhpFileIndex;
use TimLappe\Elephactor\Domain\Psr4\Adapter\Psr4ClassLikeIndex;
use TimLappe\Elephactor\Domain\Psr4\Model\Psr4AutoloadMap;
use TimLappe\Elephactor\Domain\Workspace\Model\Filesystem\File;
use TimLappe\Elephactor\Domain\Workspace\Model\Workspace;

abstract class ElephactorTestCase extends TestCase
{
    protected Workspace $workspace;
    protected VirtualDirectory $sourceDirectory;
    protected Application $application;

    public function setUp(): void
    {
        $workDir = new VirtualDirectory('workdir');

        $this->workspace = new Workspace(
            $workDir,
            new Environment($this->phpVersion()),
        );

        $this->sourceDirectory = $workDir->createOrGetDirecotry('src');

        $psr4AutoloadMap = new Psr4AutoloadMap();
        $psr4AutoloadMap->add(new QualifiedName([new Identifier('VirtualTestNamespace')]), $this->sourceDirectory);

        $nikicAstBuilder = new NikicAstBuilder(new NikicToDomainNodeMapper(), $this->workspace->environment()->phpVersion());

        $psr4FileIndex = new Psr4PhpFileIndex($psr4AutoloadMap, new PhpFileRepository($nikicAstBuilder));
        $psr4FileIndex->reload();

        $this->workspace->registerPhpFileIndex($psr4FileIndex);
        $this->workspace->registerClassLikeIndex(new Psr4ClassLikeIndex($psr4FileIndex));
        $this->workspace->reloadIndices();

        $this->application = new Application($this->workspace);
    }

    protected function codeMatches(string $code, string $expectedCode): void
    {
        self::assertEquals($this->normalizeCode($expectedCode), $this->normalizeCode($code));
    }

    protected function mountFixtureTree(string $absoluteFixtureRootPath): void
    {
        VirtualWorkspaceFixtureLoader::mirrorOnto(
            $this->sourceDirectory,
            $absoluteFixtureRootPath,
        );
    }

    protected function assertVirtualFileMatchesExpectedPath(File $file, string $absoluteExpectedFilePath): void
    {
        self::assertFileExists($absoluteExpectedFilePath);
        $expected = file_get_contents($absoluteExpectedFilePath);
        self::assertNotFalse($expected, sprintf('Could not read expected file: %s', $absoluteExpectedFilePath));
        $this->codeMatches($file->content(), $expected);
    }

    protected function virtualFileUnderSource(string ...$pathSegments): File
    {
        if ($pathSegments === []) {
            throw new \InvalidArgumentException('At least the file name is required.');
        }

        $fileName = array_pop($pathSegments);
        $directory = $this->sourceDirectory;
        foreach ($pathSegments as $segment) {
            $directory = $directory->createOrGetDirecotry($segment);
        }

        $file = $directory
            ->childFiles()
            ->first(static fn (File $candidate): bool => $candidate->name() === $fileName);

        if ($file === null) {
            self::fail(sprintf('Virtual file %s not found under source directory.', $fileName));
        }

        return $file;
    }

    protected function phpVersion(): PhpVersion
    {
        return PhpVersion::fromString('8.3');
    }

    private function normalizeCode(string $code): string
    {
        while (strpos($code, "  ") !== false) {
            $code = str_replace("  ", " ", $code);
        }

        return $code;
    }
}
