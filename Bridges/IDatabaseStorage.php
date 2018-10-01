<?php


namespace vojtabiberle\MediaStorage\Bridges;


use vojtabiberle\MediaStorage\Files\IFile;
use vojtabiberle\MediaStorage\IFileFilter;

interface IDatabaseStorage
{
    public function store(IFile $file);
    public function find(IFileFilter $filter);
    public function get($name, $namespace);
    public function saveUsage($data);
    public function removeUsage($data);
    public function setPrimary($namespace, $mediaId);
    public function deleteFiles(IFileFilter $filter);
    public function deleteById($id);
    public function deleteUsageByNamespace($ns);
}