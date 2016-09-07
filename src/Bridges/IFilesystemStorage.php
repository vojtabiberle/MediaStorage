<?php

namespace vojtabiberle\MediaStorage\Bridges;

use Psr\Http\Message\UploadedFileInterface;
use vojtabiberle\MediaStorage\Files\IFile;

interface IFilesystemStorage
{
    public static function mkdir($pathname, $mode = 0777, $recursive = false);
    public static function findFiles($basePath, $pattern);
    public static function deleteFiles($paths);
    public static function deleteFile($path);
}