<?php

namespace vojtabiberle\MediaStorage\Bridges\Nette\Utils;

use Nette\NotSupportedException;
use Nette\Utils\Callback;
use Nette\Utils\ImageException;
use Nette\Utils\UnknownImageFileException;
use vojtabiberle\MediaStorage\Bridges\IFileUpload;
use vojtabiberle\MediaStorage\Images\IImage;
use vojtabiberle\MediaStorage\Utils;

class Image
    extends \Nette\Utils\Image
    implements IImage
{
    private $UID;
    private $name;
    private $full_path;
    private $size;
    private $content_type;
    private $is_image = true;
    private $primary = false;
    private $namespace;
    private $storage_path;

    /** @var integer */
    private $quality;

    /**
     * @param integer $quality
     * @return self
     */
    public function setQuality($quality) {
        $this->quality = $quality;
        return $this;
    }

    public function getQuality()
    {
        return $this->quality;
    }

    /**
     * Saves image to the file.
     *
     * @param  string $filename
     * @param  int    $quality 0..100 (for JPEG and PNG)
     * @param  string $type    image type
     * @return bool TRUE on success or FALSE on failure.
     */
    public function save($file = NULL, $quality = NULL, $type = NULL) {
        if (!is_null($file)) {
            $this->full_path = $file;
            $parts = explode(DIRECTORY_SEPARATOR, $file);
            $name = array_pop($parts);
            $this->name = $name;
        }
        return parent::save($file, $quality ? $quality : $this->getQuality(), $type ? $type : $this->getNetteImageType());
    }

    public function __construct($image = null, $name = null, $full_path = null, $size = null, $content_type = null, $is_image = null)
    {
        if (is_null($image) && !is_null($full_path)) {
            $image = self::createResource($full_path);
        }
        if (is_resource($image) && get_resource_type($image) === 'gd') {
            parent::__construct($image);
        }

        $this->name = $name;
        $this->full_path = $full_path;
        $this->size = $size;
        $this->content_type = $content_type;
    }

    /**
     * @return mixed
     */
    public function getDatabaseId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setDatabaseId($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getFullPath()
    {
        return $this->full_path;
    }

    public function getSize()
    {
        if (is_null($this->size)) {
            $this->size = filesize($this->getFullPath());
        }
        return $this->size;
    }

    public function getContentType()
    {
        if (is_null($this->content_type)) {
            $this->content_type = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $this->getFullPath());
        }
        return $this->content_type;
    }

    private function getNetteImageType()
    {
        switch ($this->getContentType()) {
            case 'image/jpg':
            case 'image/jpeg':
                return self::JPEG;
            case 'image/png':
                return self::PNG;
            case 'image/gif':
                return self::GIF;
        }
    }

    public function isImage()
    {
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

    public function getContent()
    {
        return file_get_contents($this->getFullPath());
    }

    private static function createResource($file)
    {
        if (!extension_loaded('gd')) {
            throw new NotSupportedException('PHP extension GD is not loaded.');
        }

        static $funcs = [
            self::JPEG => 'imagecreatefromjpeg',
            self::PNG => 'imagecreatefrompng',
            self::GIF => 'imagecreatefromgif',
        ];
        $info = @getimagesize($file); // @ - files smaller than 12 bytes causes read error
        $format = $info[2];

        if (!isset($funcs[$format])) {
            throw new UnknownImageFileException(is_file($file) ? "Unknown type of file '$file'." : "File '$file' not found.");
        }
        return Callback::invokeSafe($funcs[$format], [$file], function ($message) {
            throw new ImageException($message);
        });
    }

    public function __clone()
    {
        $this->full_path = null;
        if (!is_null($this->imageResource)) {
            parent::__clone();
        }
    }

    public function fillFileUpload(IFileUpload $fileUpload)
    {
        $this->name = $fileUpload->getName();
        $this->size = $fileUpload->getSize();
        $this->content_type = $fileUpload->getContentType();
        $this->is_image = true;
        if (is_null($this->UID)) {
            $this->generateUID();
        }

        $fullPath = $this->storage_path . DIRECTORY_SEPARATOR . $this->UID;
        $fileUpload->moveTo($fullPath);
        $this->full_path = $fullPath;

        $image = self::createResource($fullPath);
        $this->setImageResource($image);
        imagesavealpha($image, TRUE);
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

        $image = self::createResource($this->full_path);
        $this->setImageResource($image);
        imagesavealpha($image, TRUE);
    }

    public function setStoragePath($path)
    {
        if (is_null($this->storage_path)) {
            $this->storage_path = $path;
        }
        // set only once. should I throw Exception?
        // or allow unlimited setting and move file?
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

    public function getIconName()
    {
        if(strpos($this->name, '.')) {
            list($name, $ext) = explode('.', $this->name, 2);
            return $ext.'.png';
        }

        return false;
    }
}