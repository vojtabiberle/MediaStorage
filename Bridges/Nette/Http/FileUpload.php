<?php

namespace vojtabiberle\MediaStorage\Bridges\Nette\Http;

use Nette;
use Nette\Utils\Strings;
use Psr\Http\Message\StreamInterface;
use vojtabiberle\MediaStorage\Bridges\IFileUpload;
use Zend\Diactoros\Stream;

/**
 * Nette really should adopt UploadFileInterface
 *
 * Class FileUpload
 * @package vojtabiberle\MediaStorage\Bridges\Nette\Http
 */
class FileUpload extends \Nette\Http\FileUpload implements IFileUpload
{

    private $moved = false;


    /**
     * @param Nette\Http\FileUpload $fileUpload
     * @return self
     */
    public static function createFromNetteFileUpload(Nette\Http\FileUpload $fileUpload)
    {
        $new = new self([
            'name' => Strings::webalize($fileUpload->getName(),'_.'),
            'type' => $fileUpload->getContentType(),
            'size' => $fileUpload->getSize(),
            'tmp_name' => $fileUpload->getTemporaryFile(),
            'error' => $fileUpload->getError(),
        ]);

        return $new;
    }

    /**
     * Retrieve a stream representing the uploaded file.
     *
     * This method MUST return a StreamInterface instance, representing the
     * uploaded file. The purpose of this method is to allow utilizing native PHP
     * stream functionality to manipulate the file upload, such as
     * stream_copy_to_stream() (though the result will need to be decorated in a
     * native PHP stream wrapper to work with such functions).
     *
     * If the moveTo() method has been called previously, this method MUST raise
     * an exception.
     *
     * @return StreamInterface Stream representation of the uploaded file.
     * @throws \RuntimeException in cases when no stream is available or can be
     *     created.
     */
    public function getStream()
    {
        if ($this->moved) {
            throw new \RuntimeException('File was moved!');
        }

        return new Stream($this->getContents());
    }

    /**
     * Move the uploaded file to a new location.
     *
     * Use this method as an alternative to move_uploaded_file(). This method is
     * guaranteed to work in both SAPI and non-SAPI environments.
     * Implementations must determine which environment they are in, and use the
     * appropriate method (move_uploaded_file(), rename(), or a stream
     * operation) to perform the operation.
     *
     * $targetPath may be an absolute path, or a relative path. If it is a
     * relative path, resolution should be the same as used by PHP's rename()
     * function.
     *
     * The original file or stream MUST be removed on completion.
     *
     * If this method is called more than once, any subsequent calls MUST raise
     * an exception.
     *
     * When used in an SAPI environment where $_FILES is populated, when writing
     * files via moveTo(), is_uploaded_file() and move_uploaded_file() SHOULD be
     * used to ensure permissions and upload status are verified correctly.
     *
     * If you wish to move to a stream, use getStream(), as SAPI operations
     * cannot guarantee writing to stream destinations.
     *
     * @see http://php.net/is_uploaded_file
     * @see http://php.net/move_uploaded_file
     * @param string $targetPath Path to which to move the uploaded file.
     * @throws \InvalidArgumentException if the $path specified is invalid.
     * @throws \RuntimeException on any error during the move operation, or on
     *     the second or subsequent call to the method.
     */
    public function moveTo($targetPath)
    {
        $this->move($targetPath);
        $this->moved = true;
    }

    public function move($dest)
    {
        $source = $this->getTemporaryFile();
        $result = parent::move($dest);
        @unlink($source);
        $this->moved = true;
        return $result;
    }

    /**
     * Retrieve the filename sent by the client.
     *
     * Do not trust the value returned by this method. A client could send
     * a malicious filename with the intention to corrupt or hack your
     * application.
     *
     * Implementations SHOULD return the value stored in the "name" key of
     * the file in the $_FILES array.
     *
     * @return string|null The filename sent by the client or null if none
     *     was provided.
     */
    public function getClientFilename()
    {
        return $this->getName();
    }

    /**
     * Retrieve the media type sent by the client.
     *
     * Do not trust the value returned by this method. A client could send
     * a malicious media type with the intention to corrupt or hack your
     * application.
     *
     * Implementations SHOULD return the value stored in the "type" key of
     * the file in the $_FILES array.
     *
     * @return string|null The media type sent by the client or null if none
     *     was provided.
     */
    public function getClientMediaType()
    {
        return $this->getContentType();
    }

    public function isImage()
    {
        return exif_imagetype($this->getTemporaryFile()) > 0;
    }
}