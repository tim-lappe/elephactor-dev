<?php

declare(strict_types=1);

namespace TimLappe\ElephactorTests\Application;

use PHPUnit\Framework\TestCase;
use TimLappe\Elephactor\Adapter\Composer\ComposerConfigJsonLoader;
use TimLappe\Elephactor\Adapter\Workspace\FsAbsolutePath;
use TimLappe\Elephactor\Adapter\Workspace\FsDirectory;
use TimLappe\Elephactor\Adapter\Workspace\FsFile;
use TimLappe\Elephactor\Domain\Psr4\Model\Psr4AutoloadMapItem;

final class ComposerConfigJsonLoaderTest extends TestCase
{
    private ?string $projectDirectory = null;

    protected function tearDown(): void
    {
        if ($this->projectDirectory !== null) {
            $this->removeDirectory($this->projectDirectory);
        }
    }

    public function testLoadsMultiplePsr4PathsForOneNamespace(): void
    {
        $this->createComposerProject([
            'autoload' => [
                'psr-4' => [
                    'Fewo\\' => 'src/',
                    'Fewo\\Email\\' => ['email/php/src/', 'email/php/generated/'],
                ],
            ],
        ]);

        $loader = new ComposerConfigJsonLoader();
        $config = $loader->load(new FsFile(new FsAbsolutePath($this->projectDirectory . '/composer.json')));
        $autoloadMap = $config->autoload()->psr4AutoloadMap();

        self::assertNotNull($autoloadMap);
        self::assertSame(
            [
                ['Fewo', $this->projectDirectory . '/src'],
                ['Fewo\Email', $this->projectDirectory . '/email/php/src'],
                ['Fewo\Email', $this->projectDirectory . '/email/php/generated'],
            ],
            array_map(
                static fn (Psr4AutoloadMapItem $item): array => [
                    $item->namespace()->__toString(),
                    self::directoryPath($item),
                ],
                $autoloadMap->getItems(),
            ),
        );
    }

    public function testIgnoresMissingPsr4Paths(): void
    {
        $this->createComposerProject([
            'autoload' => [
                'psr-4' => [
                    'Fewo\\' => 'src/',
                ],
            ],
            'autoload-dev' => [
                'psr-4' => [
                    'Fewo\\Tests\\' => 'tests/',
                    'Fewo\\PhpStan\\' => 'tools/phpstan/',
                ],
            ],
        ], [
            'src',
            'tests',
        ]);

        $loader = new ComposerConfigJsonLoader();
        $config = $loader->load(new FsFile(new FsAbsolutePath($this->projectDirectory . '/composer.json')));

        self::assertSame(
            [
                ['Fewo\Tests', $this->projectDirectory . '/tests'],
            ],
            array_map(
                static fn (Psr4AutoloadMapItem $item): array => [
                    $item->namespace()->__toString(),
                    self::directoryPath($item),
                ],
                $config->autoloadDev()->psr4AutoloadMap()?->getItems() ?? [],
            ),
        );
    }

    private static function directoryPath(Psr4AutoloadMapItem $item): string
    {
        $directory = $item->directory();
        if (!$directory instanceof FsDirectory) {
            throw new \RuntimeException(sprintf('Directory %s is not a filesystem directory', $directory->name()));
        }

        return $directory->absolutePath()->value();
    }

    /**
     * @param array<mixed> $composerJson
     * @param list<string> $directories
     */
    private function createComposerProject(array $composerJson, array $directories = [
        'src',
        'email/php/src',
        'email/php/generated',
    ]): void
    {
        $this->projectDirectory = sys_get_temp_dir() . '/elephactor-composer-loader-' . bin2hex(random_bytes(8));

        foreach ($directories as $directory) {
            if (!mkdir($this->projectDirectory . '/' . $directory, 0777, true) && !is_dir($this->projectDirectory . '/' . $directory)) {
                throw new \RuntimeException(sprintf('Could not create directory %s', $directory));
            }
        }

        $projectDirectory = realpath($this->projectDirectory);
        if ($projectDirectory === false) {
            throw new \RuntimeException(sprintf('Could not resolve project directory %s', $this->projectDirectory));
        }

        $this->projectDirectory = $projectDirectory;
        file_put_contents($this->projectDirectory . '/composer.json', json_encode($composerJson, JSON_THROW_ON_ERROR));
    }

    private function removeDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = scandir($directory);
        if ($items === false) {
            throw new \RuntimeException(sprintf('Could not read directory %s', $directory));
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . '/' . $item;
            if (is_dir($path)) {
                $this->removeDirectory($path);
                continue;
            }

            if (!unlink($path)) {
                throw new \RuntimeException(sprintf('Could not remove file %s', $path));
            }
        }

        if (!rmdir($directory)) {
            throw new \RuntimeException(sprintf('Could not remove directory %s', $directory));
        }
    }
}
