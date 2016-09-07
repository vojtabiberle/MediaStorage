<?php

namespace vojtabiberle\MediaStorage;

use Nette\Database\Table\ActiveRow;
use vojtabiberle\MediaStorage\Bridges\FilesystemStorage;
use vojtabiberle\MediaStorage\Bridges\IDatabaseStorage;
use vojtabiberle\MediaStorage\Bridges\IFilesystemStorage;
use vojtabiberle\MediaStorage\Bridges\IFileUpload;
use vojtabiberle\MediaStorage\Exceptions\FileNotFoundException;
use vojtabiberle\MediaStorage\Exceptions\StorageException;
use vojtabiberle\MediaStorage\Files\IFile;
use vojtabiberle\MediaStorage\Images\IImage;
use vojtabiberle\MediaStorage\Images\Image;

class Storage implements IStorage
{
    /** @var  IDatabaseStorage */
    private $databaseStorage;

    /** @var  IFilesystemStorage */
    private $filesystemStorage;

    private static $mediaStoragePath;
    private static $fileClass;
    private static $imageClass;

    /**
     * @var IFileFilter $currentFilter
     */
    private $currentFilter = null;

    public function __construct(
        IDatabaseStorage $databaseStorage,
        IFilesystemStorage $filesystemStorage,
        $mediaStoragePath,
        $fileClass = 'vojtabiberle\\MediaStorage\\Files\\File',
        $imageClass = 'vojtabiberle\\MediaStorage\\Images\\Image'
    )
    {
        $this->databaseStorage = $databaseStorage;
        $this->filesystemStorage = $filesystemStorage;
        self::$mediaStoragePath = $mediaStoragePath;
        if (!file_exists(self::$mediaStoragePath)) {
            FilesystemStorage::mkdir(self::$mediaStoragePath);
        }

        self::$fileClass = $fileClass;
        self::$imageClass = $imageClass;
    }

    /**
     * @return IFile
     */
    public static function fileObjectBuilder()
    {
        /** @var IFile $file */
        $file = new self::$fileClass;
        $file->setStoragePath(self::$mediaStoragePath);
        return $file;
    }

    /**
     * @return IImage
     */
    public static function imageObjectBuilder()
    {
        /** @var IImage $image */
        $image = new self::$imageClass;
        $image->setStoragePath(self::$mediaStoragePath);
        return $image;
    }

    /**
     * Save file informations into database and file on disk
     *
     * @param IFile $file
     * @return IFile
     */
    public function save(IFile $file)
    {
        $file->setStoragePath(self::$mediaStoragePath);
        if(is_null($file->getUID())) {
            $file->generateUID();
        }
        $newPath = self::$mediaStoragePath . DIRECTORY_SEPARATOR . $file->getUID();
        $file->save($newPath);
        $this->databaseStorage->store($file);
        return $file;
    }

    public function delete(IFile $file, $webRoot)
    {
        $files = $this->databaseStorage->deleteFiles(FileFilter::create()->findByName($file->getName()));

        foreach($files as $file) {
            $this->deleteById($file->id, $webRoot);
        }

        return true; //?
    }

    public function deleteById($id, $webRoot)
    {
        $filter = FileFilter::create()->getbyId($id);
        /** @var ActiveRow $file */
        $file = $this->databaseStorage->find($filter);
        if (!$file) {
            throw new FileNotFoundException('File not found.');
        }
        $this->databaseStorage->deleteById($file->id);
        FilesystemStorage::deleteFile(self::$mediaStoragePath . DIRECTORY_SEPARATOR . $file->id);
        /** TODO: Nepředávat webRoot path k media z Manageru do Storage */
        FilesystemStorage::deleteFiles(FilesystemStorage::findFiles($webRoot.'/media/', $file->name));

        return true; //?
    }

    public function deleteUsageByNamespace($ns)
    {
        return $this->databaseStorage->deleteUsageByNamespace($ns);
    }

    /**
     * Save uploaded file into database and on disk
     *
     * @param IFileUpload $uploadedFile
     * @return mixed
     */
    public function saveUploaded(IFileUpload $uploadedFile)
    {
        if ($uploadedFile->isImage()) {
            $file = self::imageObjectBuilder();
        } else {
            $file = self::fileObjectBuilder();
        }

        /** @var IFile $file */
        $file->fillFileUpload($uploadedFile);

        $this->databaseStorage->store($file);
        return $file;
    }


    /**
     * Find files
     *
     * @param IFileFilter $filter
     * @return array[] of IFile or IImage
     */
    public function find(IFileFilter $filter)
    {
        $this->currentFilter = $filter;
        $data = $this->databaseStorage->find($filter);
        if (!$data) {
            throw new FileNotFoundException('File not found.');
        }
        return $this->hydrateRowContainer($data);
    }

    /**
     * Try to find file in database based on name and namespace
     *
     * @param $name
     * @param null $namespace
     * @return IFile|IImage
     * @throws StorageException
     */
    public function get($name, $namespace = null)
    {
        /** @var ActiveRow $data */
        $data = $this->databaseStorage->get($name, $namespace);
        if (!$data) {
            throw new FileNotFoundException('File not found.');
        }

        return $this->hydrateRow($data);
    }

    public function getNoimage($name)
    {
        $name = str_replace('noimage-', '', $name);
        $path = self::$mediaStoragePath . DIRECTORY_SEPARATOR . 'noimage';
        if (file_exists($path . DIRECTORY_SEPARATOR . $name)) {
            return Image::fromFile($path . DIRECTORY_SEPARATOR . $name);
        }

        throw new FileNotFoundException('File not found!');
    }

    /**
     * @param IFile $file
     * @param $path
     * @return IFile
     */
    public function publishFile(IFile $file, $path)
    {
        $onlyPath = dirname($path);
        if (!file_exists($onlyPath)) {
            FilesystemStorage::mkdir($onlyPath, 0777, true);
        }

        $file->save($path);

        return $file;
    }

    /**
     * @param string $namespace
     * @param array|\Traversable $used
     * @param array|\Traversable $removed
     * @return mixed
     */
    public function saveUsages($namespace, $used, $removed)
    {
       if (
       is_string($namespace) &&
       ( is_array($used) || ($used instanceof \Traversable || is_null($used)) ) &&
       ( is_array($removed) || ($removed instanceof \Traversable) || is_null($removed))
       ) {
           $return = [];
           if (!is_null($used)) {
               foreach ($used as $usage) {
                   $return['used'][] = $this->databaseStorage->saveUsage([
                       'namespace' => $namespace,
                       'media_id' => $usage,
                   ]);
               }
           }
           if (!is_null($removed)) {
               foreach ($removed as $remove) {
                   $return['removed'][] = $this->databaseStorage->removeUsage([
                       'namespace' => $namespace,
                       'media_id' => $remove,
                   ]);
               }
           }

           return $return;
       } else {
        throw new \InvalidArgumentException('Arguments must by "(string) $namespace", "(array) $used" and "(array) $removed"".');
       }
    }

    /**
     * @param string $namepsace
     * @param string|bool $mediaId if string set primary for concrete media_id, if false, unset primary for whole namespace
     * @return mixed
     */
    public function setPrimary($namespace, $mediaId)
    {
        return $this->databaseStorage->setPrimary($namespace, $mediaId);
    }

    /**
     * @param $row
     * @return mixed
     */
    private function hydrateRow($row)
    {
        if (isset($row['is_image'])) {
            if ($row['is_image'] == 1) {
                $file = self::imageObjectBuilder();
            } else {
                $file = self::fileObjectBuilder();
            }
            $file->fillData($row);
            return $file;
        }

        throw new StorageException('Can\'not create file or image object from data.');
    }

    private function hydrateRowContainer($rowContainer)
    {
        if (is_array($this->currentFilter->getPairs())) {
            return $rowContainer;
        }

        if ($this->currentFilter->isFind()) {
            $rows = [];

            foreach ($rowContainer as $row) {
                $rows[] = $this->hydrateRow($row);
            }

            return $rows;
        }

        if ($this->currentFilter->isGet()) {
            return $this->hydrateRow($rowContainer);
        }

        throw new StorageException('Can\'not determine hydratation mode.');
    }
}