<?php

namespace vojtabiberle\MediaStorage;

use vojtabiberle\MediaStorage\Bridges\IDatabaseStorage;
use vojtabiberle\MediaStorage\Bridges\IFilesystemStorage;
use vojtabiberle\MediaStorage\Bridges\IFileUpload;
use vojtabiberle\MediaStorage\Exceptions\StorageException;
use vojtabiberle\MediaStorage\Files\IFile;
use vojtabiberle\MediaStorage\Images\IImage;

interface IStorage
{
    public function __construct(
        IDatabaseStorage $databaseStorage,
        IFilesystemStorage $filesystemStorage,
        $mediaStoragePath,
        $fileClass = 'vojtabiberle\\MediaStorage\\Files\\File',
        $imageClass = 'vojtabiberle\\MediaStorage\\Images\\Image'
    );

    /**
     * Save file informations into database and file on disk
     *
     * @param IFile $file
     * @return IFile
     */
    public function save(IFile $file);

    /**
     * Delete all files on disk
     *
     * @param IFile $file
     * @return mixed
     */
    public function delete(IFile $file, $webRoot);

    /**
     * Delete all files on disk and database
     *
     * @param $id
     * @return mixed
     */
    public function deleteById($id, $webRoot);

    /**
     * Delete all usages by namespace
     *
     * @param $ns
     * @return mixed
     */
    public function deleteUsageByNamespace($ns);

    /**
     * Save uploaded file into database and on disk
     *
     * @param IFileUpload $uploadedFile
     * @return mixed
     */
    public function saveUploaded(IFileUpload $uploadedFile);

    /**
     * Find files
     *
     * @param IFileFilter $filter
     * @return array[] of IFile or IImage
     */
    public function find(IFileFilter $filter);

    /**
     * Try to find file in database based on name and namespace
     *
     * @param $name
     * @param null $namespace
     * @return IFile|IImage
     * @throws StorageException
     */
    public function get($name, $namespace = null);

    public function getNoimage($name);

    /**
     * @param IFile $file
     * @param $path
     * @return IFile
     */
    public function publishFile(IFile $file, $path);

    /**
     * @param $namespace
     * @param $usages array of uids or single uid
     * @return mixed
     */
    public function saveUsages($namespace, $used, $removed);

    /**
     * @param string $namepsace
     * @param string|bool $mediaId if string set primary for concrete media_id, if false, unset primary for whole namespace
     * @return mixed
     */
    public function setPrimary($namepsace, $mediaId);
}