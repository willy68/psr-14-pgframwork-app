<?php

namespace PgFramework\File;

class FileUtils
{
    /**
     * Filtre les fichiers avec l'extension $ext recursivement a partir de path
     *
     * @param string $path
     * @param string $ext
     * @return array
     */
    public static function getFiles(string $path, string $ext = 'php'): array
    {
        // from https://stackoverflow.com/a/41636321
        return iterator_to_array(
            new \CallbackFilterIterator(
                new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator(
                        $path,
                        \FilesystemIterator::FOLLOW_SYMLINKS | \FilesystemIterator::SKIP_DOTS
                    )
                ),
                function (\SplFileInfo $file) use ($ext) {
                    return $file->isFile() &&
                        ('.' !== substr($file->getBasename(), 0, 1) &&
                            '.' . $ext === substr($file->getFilename(), -4));
                }
            )
        );
    }
}
