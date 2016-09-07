<?php

namespace vojtabiberle\MediaStorage;

use MediaStorage\Exceptions\NoFileUploadedException;
use MediaStorage\Images\Helpers\Resize;
use Nette\Http\FileUpload;
use Psr\Http\Message\UploadedFileInterface;
use vojtabiberle\MediaStorage\Bridges\IFileUpload;
use vojtabiberle\MediaStorage\Bridges\IFormFieldFileChoicer;
use vojtabiberle\MediaStorage\Exceptions\FileNotFoundException;
use vojtabiberle\MediaStorage\Exceptions\RuntimeException;
use vojtabiberle\MediaStorage\Files\IFile;
use vojtabiberle\MediaStorage\Images\IImage;
use vojtabiberle\MediaStorage\Images\Image;

class Manager implements IManager
{
    /** @var  IStorage */
    private $storage;

    private $config;

    private $shouldPublishFile = true;

    public function __construct(IStorage $storage, $config)
    {
        $this->storage = $storage;
        $this->config = $config;
    }

    /**
     * @param IFile|IImage|IFileUpload|FileUpload|UploadedFileInterface $file
     * @return IFile|IImage
     */
    public function save($file)
    {
        if ($file instanceof IFile || $file instanceof IImage) {
            return $this->storage->save($file);
        }

        if ($file instanceof UploadedFileInterface || $file instanceof IFileUpload) {
            return $this->storage->saveUploaded($file);
        }

        if ($file instanceof FileUpload) {
            $netteUpload = \vojtabiberle\MediaStorage\Bridges\Nette\Http\FileUpload::createFromNetteFileUpload($file);
            return $this->storage->saveUploaded($netteUpload);
        }

        throw new NoFileUploadedException('Can\'not save object of type "'.get_class($file).'"');
    }

    public function delete(IFile $file)
    {
        return $this->storage->delete($file, $this->config['wwwDir']);
    }

    public function deleteById($id)
    {
        return $this->storage->deleteById($id, $this->config['wwwDir']);
    }

    /**
     * Find files based od filter
     *
     * @param IFileFilter $filter
     * @return array[] of IFiles or IImages
     */
    public function find(IFileFilter $filter)
    {
        return $this->storage->find($filter);
    }

    public function publishIcon($name, $size = null)
    {
        $icon = $this->createIcon($name);
        if (!is_null($size)) {
            $size = str_replace('size-', '', $size);
            $this->applyResizes($icon, $size);
        }

        return $this->decidePublish($icon);
    }

    public function publishFile($name, $namespace = null, $size = null, $filters = null, $noimage = null)
    {
        $file = $this->findFile($name, $namespace, $noimage);

        if ($file->isImage()) {
            /** @var IImage $image */
            $image = clone $file;

            if (!is_null($size)) {
                $size = str_replace('size-', '', $size);
                $this->applyResizes($image, $size);
            }

            if (!is_null($filters)) {
                $filters = str_replace('filter-', '', $filters);
                $this->applyFilters($image, $filters);
            }

            $file = $image;
        }

        return $this->decidePublish($file);
    }

    /**
     * @param $namespace
     * @param $usages array of uids or single uid
     * @return mixed
     */
    public function saveUsages($namespace, $used, $removed)
    {
        if (is_string($used)) {
            $used = [$used];
        }

        if (is_string($removed)) {
            $removed = [$removed];
        }

        return $this->storage->saveUsages($namespace, $used, $removed);
    }

    public function setPrimary($namespace, $mediaId)
    {
        return $this->storage->setPrimary($namespace, $mediaId);
    }

    public function saveFormFieldUsages(IFormFieldFileChoicer $formField)
    {
        $namespace = $formField->getNamespace();
        $single = $formField->isSingle();
        $used = $formField->getUsedIds();
        $removed = $formField->getRemovedIds();
        $primary = $formField->getPrimaryId();

        if ($single && count($used) > 0) {
            $this->storage->deleteUsageByNamespace($namespace);
        }

        $this->storage->saveUsages($namespace, $used, $removed);

        if (!$single && !empty($primary)) {
            $this->storage->setPrimary($namespace, $primary);
        }
    }

    public function duplicateUsage($fromNs, $toNs)
    {
        $fromFiles = $this->storage->find(FileFilter::create()->findByNamespace($fromNs.'%'));

        $results = [];
        /** @var IFile $file */
        foreach ($fromFiles as $file) {
            $newNs = str_replace($fromNs, $toNs, $file->getNamespace());
            $results[] = $this->storage->saveUsages($newNs, [$file->getUID()], null);
        }
        return $results;
    }

    private function decidePublish(IFile $file)
    {
        if($this->shouldPublishFile) {
            //TODO: sanitize REQUEST_URI - in Nette is sanitization done by Router - malfuncional string don't pass Router
            return $this->storage->publishFile($file, $this->config['wwwDir'] . $_SERVER['REQUEST_URI']);
        } else {
            return $file;
        }
    }

    /**
     * @param $name
     * @param $namespace
     * @param $noimage
     * @return IFile
     */
    private function findFile($name, $namespace = null, $noimage = null)
    {
        try {
            /** @var IFile|IImage $file */
            $this->shouldPublishFile = true;
            $file = $this->storage->get($name, $namespace);
        } catch (RuntimeException $e) {
            $this->shouldPublishFile = false;
            if (!is_null($noimage)) {
                $file = $this->storage->getNoimage($noimage);
            } else {
                throw new FileNotFoundException('File not found!');
            }
        }

        return $file;
    }

    /**
     * @param $name
     * @return IImage
     */
    private function createIcon($name)
    {
        //TODO: make it configurable!
        $dir = __DIR__.DIRECTORY_SEPARATOR.'icons';
        $iconTheme = 'teambox';
        $unknownFileIcon = '_blank.png';

        if (file_exists($dir.DIRECTORY_SEPARATOR.$iconTheme.DIRECTORY_SEPARATOR.$name)) {
            $this->shouldPublishFile = true;
            return Image::fromFile($dir.DIRECTORY_SEPARATOR.$iconTheme.DIRECTORY_SEPARATOR.$name);
        }

        if (file_exists($dir.DIRECTORY_SEPARATOR.$iconTheme.DIRECTORY_SEPARATOR.$unknownFileIcon)) {
            $this->shouldPublishFile = false;
            return Image::fromFile($dir.DIRECTORY_SEPARATOR.$iconTheme.DIRECTORY_SEPARATOR.$unknownFileIcon);
        }

        return false;
    }

    private function applyResizes(IImage &$image, $size)
    {
        $sizes = $this->config['imageManager']['sizes'];
        if (array_key_exists($size, $sizes)) {
            $size = $sizes[$size];
        }

        $resizer = new Resize();
        $resizer($image, $size);
    }

    private function applyFilters(IImage &$image, $params)
    {
        $helpers = $this->config['imageManager']['helpers'];

        if (false === strpos($params, '-')) {
            $filters[] = $params;
        } else {
            $filters = explode('-', $params);
        }

        foreach ($filters as $filter) {
            if (false !== strpos($filter, ':')) {
                list ($filter, $params) = explode(':', $filter);
            } else {
                list ($filter, $params) = [$filter, null];
            }

            if (array_key_exists($filter, $helpers)) {
                $helper = new $helpers[$filter];
                $helper($image, $params);
            }
        }
    }
}