<?php

namespace vojtabiberle\MediaStorage\Files;

use MediaStorage\Files\AbstractFile;
use vojtabiberle\MediaStorage\Bridges\FilesystemStorage;
use vojtabiberle\MediaStorage\Bridges\IFileUpload;
use vojtabiberle\MediaStorage\Utils;

class File implements IFile
{
    /** @var  int */
    protected $UID;

    /** @var string */
    protected $name;

    /** @var string */
    protected $full_path;

    /** @var int */
    protected $size;

    /** @var string */
    protected $content_type;

    /** @var bool */
    protected $is_image;

    /** @var bool */
    protected $primary;

    /** @var  string */
    protected $namespace;

    protected $storage_path;

    public function __construct($resource = null, $name = null, $full_path = null, $size = null, $content_type = null, $is_image = null)
    {
        $this->name = $name;
        $this->full_path = $full_path;
        $this->size = $size;
        $this->content_type = $content_type;
        $this->is_image = $is_image;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFullPath()
    {
        return $this->full_path;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        if (is_null($this->size)) {
            $this->size = filesize($this->getFullPath());
        }
        return $this->size;
    }

    /**
     * @return string
     */
    public function getContentType()
    {
        if (is_null($this->content_type)) {
            $this->content_type = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $this->getFullPath());
        }
        return $this->content_type;
    }

    public function getIconName()
    {
        if(strpos($this->name, '.')) {
            list($name, $ext) = explode('.', $this->name, 2);
            return $ext.'.png';
        }

        return '_blank.png';
    }

    /**
     * @return bool
     */
    public function isImage()
    {
        if (is_null($this->is_image)) {
            /**
             * if exif_imagetype cant read enought bytes for determinig, if file is image,
             * returns E_NOTICE - we don't want notice and just assume, this file is not image
             */
            $this->is_image = (0 !== count(@exif_imagetype($this->getFullPath())));
        }
        return $this->is_image;
    }

    public function isPrimary()
    {
        return $this->primary;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return mixed
     */
    public function getUID()
    {
        return $this->UID;
    }

    /**
     * @param mixed $UID
     */
    public function setUID($UID)
    {
        $this->UID = $UID;
    }

    public function generateUID()
    {
        $this->UID = uniqid('', true);
    }

    public function save($path, $newName = null)
    {
        FilesystemStorage::copy($this->getFullPath(), $path);
        $this->full_path = $path;
        if (!is_null($newName)) {
            $this->name = $newName;
        }
    }

    public function getContent()
    {
        return file_get_contents($this->getFullPath());
    }

    public function __clone()
    {
        /** Nothing to do for files */
    }

    public function fillFileUpload(IFileUpload $fileUpload)
    {
        $this->name = $fileUpload->getName();
        $this->size = $fileUpload->getSize();
        $this->content_type = $fileUpload->getContentType();
        $this->is_image = $fileUpload->isImage();
        if (is_null($this->UID)) {
            $this->generateUID();
        }

        $fullPath = $this->storage_path . DIRECTORY_SEPARATOR . $this->UID;
        $fileUpload->moveTo($fullPath);
        $this->full_path = $fullPath;
    }

    public function fillData($data)
    {
        $this->UID = $data['id'];
        $this->name = $data['name'];
        $this->full_path = $data['full_path'];
        $this->size = $data['size'];
        $this->content_type = $data['content_type'];
        $this->is_image = $data['is_image'];
        if (isset($data['primary'])) {
            $this->primary = $data['primary'];
        }
        if (isset($data['namespace'])) {
            $this->namespace = $data['namespace'];
        }
    }

    public function setStoragePath($path)
    {
        if (is_null($this->storage_path)) {
            $this->storage_path = $path;
        }
        // set only once. should I throw Exception?
        // or allow unlimited setting and move file?
    }
}