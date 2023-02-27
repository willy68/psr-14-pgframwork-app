<?php

declare(strict_types=1);

namespace PgFramework\File;

use CallbackFilterIterator;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class FileUtils
{
    /**
     * Filtre les fichiers avec l’extension $ext récursivement à partir de path
     *
     * @param string $path
     * @param string $ext
     * @param string|null $exclude
     * @return array
     */
    public static function getFiles(string $path, string $ext = 'php', ?string $exclude = null): array
    {
        // from https://stackoverflow.com/a/41636321
        return iterator_to_array(
            new CallbackFilterIterator(
                new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator(
                        $path,
                        FilesystemIterator::FOLLOW_SYMLINKS | FilesystemIterator::SKIP_DOTS
                    )
                ),
                function (SplFileInfo $file) use ($ext, $exclude) {
                    return $file->isFile() &&
                        (!str_starts_with($file->getBasename(), '.') &&
                            null !== $exclude ?
                            !(stripos($file->getBasename(), $exclude)) :
                            '.' . $ext === substr($file->getFilename(), -4));
                }
            )
        );
    }
}
