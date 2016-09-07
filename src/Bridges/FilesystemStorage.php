<?php

namespace vojtabiberle\MediaStorage\Bridges;

use vojtabiberle\MediaStorage\Exceptions\FilesystemStorageException;

class FilesystemStorage implements IFilesystemStorage
{
    public static function mkdir($pathname, $mode = 0777, $recursive = false)
    {
        if (!@mkdir($pathname, $mode, $recursive) && !is_dir($pathname)) {
            throw new FilesystemStorageException('Cannot create directory: '.$pathname);
        }
        return true;
    }

    public static function findFiles($basePath, $pattern)
    {
        $iterator = new \RecursiveDirectoryIterator($basePath, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS);
        $iterator = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::CHILD_FIRST);
        $files = [];
        foreach ($iterator as $file) {
            /** @var \SplFileInfo $file */
            if ($file->isFile() && $file->getFilename() === $pattern) {
                $files[] = $file;
            }
        }
        return $files;
    }

    public static function deleteFiles($files)
    {
        array_walk($files, ['vojtabiberle\MediaStorage\Bridges\FilesystemStorage', 'deleteFile']);
    }

    public static function deleteFile($path)
    {
        return unlink($path);
    }

    public static function copy($from, $to)
    {
        return copy($from, $to);
    }

}