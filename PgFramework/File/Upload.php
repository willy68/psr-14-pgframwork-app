<?php

declare(strict_types=1);

namespace PgFramework\File;

use Intervention\Image\ImageManager;
use Psr\Http\Message\UploadedFileInterface;

class Upload
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var array
     */
    protected $formats = [];

    /**
     * @param string|null $path
     */
    public function __construct(?string $path = null)
    {
        if ($path) {
            $this->path = $path;
        }
    }

    /**
     * @param UploadedFileInterface $file
     * @param string|null $oldFile
     * @return string|null
     */
    public function upload(UploadedFileInterface $file, ?string $oldFile = null): ?string
    {
        if ($file->getError() === UPLOAD_ERR_OK) {
            $this->delete($oldFile);
            $targetPath = $this->addCopySuffix($this->path . DIRECTORY_SEPARATOR . $file->getClientFilename());
            $dirname = pathinfo($targetPath, PATHINFO_DIRNAME);
            if (!file_exists($dirname)) {
                mkdir($dirname, 0777, true);
            }
            $file->moveTo($targetPath);
            $this->generateFormats($targetPath);
            return pathinfo($targetPath)['basename'];
        }
        return null;
    }

    /**
     * @param string $targetPath
     * @return string
     */
    protected function addCopySuffix(string $targetPath): string
    {
        if (file_exists($targetPath)) {
            return $this->addCopySuffix(
                $this->getPathWithSuffix(
                    $targetPath,
                    'copy'
                )
            );
        }
        return $targetPath;
    }

    /**
     * @param string|null $oldFile
     * @return void
     */
    public function delete(?string $oldFile = null): void
    {
        if ($oldFile) {
            $oldFile = $this->path . DIRECTORY_SEPARATOR . $oldFile;
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
            foreach ($this->formats as $format => $_) {
                $oldFileFormat = $this->getPathWithSuffix($oldFile, $format);
                if (file_exists($oldFileFormat)) {
                    unlink($oldFileFormat);
                }
            }
        }
    }

    /**
     * @param string $path
     * @param string $suffix
     * @return string
     */
    private function getPathWithSuffix(string $path, string $suffix): string
    {
        $info = pathinfo($path);
        return $info['dirname'] .
        DIRECTORY_SEPARATOR . $info['filename'] .
        '_' . $suffix . '.' .
        $info['extension'];
    }

    /**
     * @param $targetPath
     */
    private function generateFormats($targetPath)
    {
        foreach ($this->formats as $format => $size) {
            $destination = $this->getPathWithSuffix($targetPath, $format);
            $manager = new ImageManager(['driver' => 'gd']);
            [$width, $height] = $size;
            $manager->make($targetPath)->fit($width, $height)->save($destination);
        }
    }
}
