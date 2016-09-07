<?php

namespace vojtabiberle\MediaStorage\Bridges\Nette\Application\Responses;

use Nette;

class FileContentResponse extends Nette\Object implements Nette\Application\IResponse
{

    /** @var string */
    private $fileContent;

    /** @var string */
    private $contentType;

    /** @var string */
    private $name;

    /** @var bool */
    public $resuming = false;

    /** @var bool */
    private $forceDownload;

    /**
     * @param  string  file path
     * @param  string  imposed file name
     * @param  string  MIME content type
     */
    public function __construct($fileContent, $name = null, $contentType = null ,$forceDownload = true)
    {
        $this->fileContent = $fileContent;
        $this->name = $name;
        $this->contentType = $contentType ? $contentType : 'application/octet-stream';
        $this->forceDownload = $forceDownload;
    }

    /**
     * Returns the path to a downloaded file.
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }


    /**
     * Returns the file name.
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * Returns the MIME content type of a downloaded file.
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Sends response to output.
     * @return void
     */
    public function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse)
    {
        $httpResponse->setContentType($this->contentType);
        $httpResponse->setHeader('Content-Disposition',
            ($this->forceDownload ? 'attachment' : 'inline')
            . '; filename="' . $this->name . '"'
            . '; filename*=utf-8\'\'' . rawurlencode($this->name));

        echo $this->fileContent;
    }
}