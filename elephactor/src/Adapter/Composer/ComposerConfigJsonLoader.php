<?php

declare(strict_types=1);

namespace TimLappe\Elephactor\Adapter\Composer;

use TimLappe\Elephactor\Adapter\Workspace\FsAbsolutePath;
use TimLappe\Elephactor\Adapter\Workspace\FsDirectory;
use TimLappe\Elephactor\Adapter\Workspace\FsFile;
use TimLappe\Elephactor\Domain\Composer\Model\Autoload;
use TimLappe\Elephactor\Domain\Composer\Model\ComposerConfig;
use TimLappe\Elephactor\Domain\Php\AST\Model\Value\QualifiedName;
use TimLappe\Elephactor\Domain\Psr4\Model\Psr4AutoloadMap;

final class ComposerConfigJsonLoader
{
    public function load(FsFile $composerJsonFile): ComposerConfig
    {
        $content = $composerJsonFile->content();
        if ($content === '') {
            throw new \RuntimeException('Composer.json is empty');
        }

        $composerJson = json_decode($content, true);
        if ($composerJson === null) {
            throw new \RuntimeException('Composer.json is not valid JSON');
        }

        if (!is_array($composerJson)) {
            throw new \RuntimeException('Composer.json is not valid JSON');
        }

        return new ComposerConfig(
            $this->loadAutoload($composerJson, $composerJsonFile),
            $this->loadAutoloadDev($composerJson, $composerJsonFile),
        );
    }

    /**
     * @param array<mixed> $composerJson
     */
    private function loadAutoload(array $composerJson, FsFile $composerJsonFile): Autoload
    {
        if (!is_array($composerJson['autoload'] ?? null)) {
            return new Autoload();
        }

        $psr4Autoload = $composerJson['autoload']['psr-4'] ?? null;
        if (!is_array($psr4Autoload)) {
            return new Autoload();
        }

        return $this->createAutoload($psr4Autoload, $composerJsonFile);
    }

    /**
     * @param array<mixed> $composerJson
     */
    private function loadAutoloadDev(array $composerJson, FsFile $composerJsonFile): Autoload
    {
        if (!is_array($composerJson['autoload-dev'] ?? null)) {
            return new Autoload();
        }

        $psr4Autoload = $composerJson['autoload-dev']['psr-4'] ?? null;
        if (!is_array($psr4Autoload)) {
            return new Autoload();
        }

        return $this->createAutoload($psr4Autoload, $composerJsonFile);
    }

    /**
     * @param array<mixed> $psr4Autoload
     */
    private function createAutoload(array $psr4Autoload, FsFile $composerJsonFile): Autoload
    {
        $psr4AutoloadMap = new Psr4AutoloadMap();
        foreach ($psr4Autoload as $namespace => $path) {
            if (!is_string($namespace)) {
                continue;
            }

            foreach ($this->normalizeAutoloadPaths($path) as $autoloadPath) {
                $autoloadPath = $this->resolveAutoloadPath($autoloadPath, $composerJsonFile);
                if ($autoloadPath === null) {
                    continue;
                }

                $psr4AutoloadMap->add(QualifiedName::fromString($namespace), new FsDirectory(new FsAbsolutePath($autoloadPath)));
            }
        }

        return new Autoload($psr4AutoloadMap);
    }

    /**
     * @return list<string>
     */
    private function normalizeAutoloadPaths(mixed $path): array
    {
        if (is_string($path)) {
            return [$path];
        }

        if (!is_array($path)) {
            return [];
        }

        $paths = [];
        foreach ($path as $autoloadPath) {
            if (!is_string($autoloadPath)) {
                continue;
            }

            $paths[] = $autoloadPath;
        }

        return $paths;
    }

    private function resolveAutoloadPath(string $path, FsFile $composerJsonFile): ?string
    {
        if (!$this->isAbsolutePath($path)) {
            $path = $composerJsonFile->directory()->absolutePath() . '/' . $path;
        }

        if (!is_dir($path)) {
            return null;
        }

        return $path;
    }

    private function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, '/') || preg_match('/^[A-Za-z]:[\/\\\\]/', $path) === 1;
    }
}
