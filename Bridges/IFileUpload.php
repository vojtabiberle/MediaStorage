<?php
namespace vojtabiberle\MediaStorage\Bridges;
use Nette;
use Psr\Http\Message\UploadedFileInterface;


/**
 * Nette realy should adopt PSR7/UploadedFileInterface
 *
 * Interface IFileUpload
 * @package Nette\Http
 */
interface IFileUpload extends UploadedFileInterface
{
    /**
     * Returns the file name.
     * @return string
     */
    public function getName();

    /**
     * Returns the sanitized file name.
     * @return string
     */
    public function getSanitizedName();

    /**
     * Returns the MIME content type of an uploaded file.
     * @return string|NULL
     */
    public function getContentType();

    /**
     * Returns the size of an uploaded file.
     * @return int
     */
    public function getSize();

    /**
     * Returns the path to an uploaded file.
     * @return string
     */
    public function getTemporaryFile();

    /**
     * Returns the error code. {@link http://php.net/manual/en/features.file-upload.errors.php}
     * @return int
     */
    public function getError();

    /**
     * Is there any error?
     * @return bool
     */
    public function isOk();

    /**
     * Move uploaded file to new location.
     * @param  string
     * @return self
     */
    public function move($dest);

    /**
     * Is uploaded file GIF, PNG or JPEG?
     * @return bool
     */
    public function isImage();

    /**
     * Returns the image.
     * @return Nette\Utils\Image
     * @throws Nette\Utils\ImageException
     */
    public function toImage();

    /**
     * Returns the dimensions of an uploaded image as array.
     * @return array|NULL
     */
    public function getImageSize();

    /**
     * Get file contents.
     * @return string|NULL
     */
    public function getContents();
}