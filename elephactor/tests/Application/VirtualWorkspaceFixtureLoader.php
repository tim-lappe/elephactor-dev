<?php

declare(strict_types=1);

namespace TimLappe\ElephactorTests\Application;

use Symfony\Component\Finder\Finder;

/**
 * Mirrors a real directory tree onto a VirtualDirectory (paths relative to the fixture root
 * become child paths under the given target root, typically ElephactorTestCase::sourceDirectory).
 */
final class VirtualWorkspaceFixtureLoader
{
    public static function mirrorOnto(VirtualDirectory $targetRoot, string $sourceAbsolutePath): void
    {
        $normalized = realpath($sourceAbsolutePath);
        if ($normalized === false || !is_dir($normalized)) {
            throw new \InvalidArgumentException(sprintf('Fixture root is not a directory: %s', $sourceAbsolutePath));
        }

        $finder = Finder::create()->files()->in($normalized)->sortByName();
        foreach ($finder as $fileInfo) {
            $realPath = $fileInfo->getRealPath();
            if ($realPath === false) {
                throw new \RuntimeException(sprintf('Could not resolve path for fixture: %s', $fileInfo->getPathname()));
            }

            $relative = self::relativePath($normalized, $realPath);
            $content = file_get_contents($realPath);
            if ($content === false) {
                throw new \RuntimeException(sprintf('Could not read fixture file: %s', $realPath));
            }

            self::createFileAtRelativePath($targetRoot, $relative, $content);
        }
    }

    private static function relativePath(string $root, string $absolutePath): string
    {
        $root = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (!str_starts_with($absolutePath, $root)) {
            throw new \InvalidArgumentException(sprintf('Path %s is not under root %s', $absolutePath, $root));
        }

        return substr($absolutePath, strlen($root));
    }

    private static function createFileAtRelativePath(VirtualDirectory $root, string $relativePath, string $content): void
    {
        $relativePath = str_replace('\\', '/', $relativePath);
        $segments = explode('/', $relativePath);
        $fileName = array_pop($segments);
        if ($fileName === '') {
            throw new \InvalidArgumentException(sprintf('Invalid relative fixture path: %s', $relativePath));
        }

        $dir = $root;
        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }

            $dir = $dir->createOrGetDirecotry($segment);
        }

        $dir->createFile($fileName, $content);
    }
}
