<?php


namespace vojtabiberle\MediaStorage;


use Nette\Http\FileUpload;
use Psr\Http\Message\UploadedFileInterface;
use vojtabiberle\MediaStorage\Bridges\IFileUpload;
use vojtabiberle\MediaStorage\Bridges\IFormFieldFileChoicer;
use vojtabiberle\MediaStorage\Files\IFile;
use vojtabiberle\MediaStorage\Images\IImage;

interface IManager
{
    public function __construct(IStorage $storage, $config);

    /**
     * @param IFile|IImage|IFileUpload|FileUpload|UploadedFileInterface $file
     * @return IFile|IImage
     */
    public function save($file);

    /**
     * Delete all usages and all files on disk
     *
     * @param IFile $file
     * @return mixed
     */
    public function delete(IFile $file);

    /**
     * Delete all files on disk and database
     *
     * @param $id
     * @return mixed
     */
    public function deleteById($id);

    /**
     * Find files based od filter
     *
     * @param IFileFilter $filter
     * @return array[] of IFiles or IImages
     */
    public function find(IFileFilter $filter);

    /**
     * Create image of given parameters, publish them to specified folder accesed by webserver and return that image.
     *
     * @param $name
     * @param $size
     * @param $filters
     * @param $noimage
     * @return IImage
     */
    public function publishFile($name, $namespace = null, $size = null, $filters = null, $noimage = null);

    /**
     * Create icon of given name or blank icon.
     *
     * @param $name
     * @param null $size
     * @return mixed
     */
    public function publishIcon($name, $size = null);

    /**
     * @param $namespace
     * @param $usages array of uids or single uid
     * @return mixed
     */
    public function saveUsages($namespace, $used, $removed);

    public function setPrimary($namespace, $mediaId);

    public function saveFormFieldUsages(IFormFieldFileChoicer $formField);
}