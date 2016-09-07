<?php

namespace vojtabiberle\MediaStorage\Bridges\Nette\Controls\AjaxUpload;

use vojtabiberle\MediaStorage\Bridges\Nette\Forms\MultiuploadFormFactory;
use vojtabiberle\MediaStorage\Bridges\Nette\Forms\UploadFormFactory;
use vojtabiberle\MediaStorage\IManager;

class AjaxUploadControlFactory
{
    /** @var  IManager */
    private $manager;

    /** @var UploadFormFactory  */
    private $uploadFormFactory;

    /** @var MultiuploadFormFactory  */
    private $multiuploadFormFactory;

    /** @var  boolean */
    private $disableMultiupload;

    public function __construct(IManager $manager, UploadFormFactory $uploadFormFactory, MultiuploadFormFactory $multiuploadFormFactory, $disableMultiupload)
    {
        $this->manager = $manager;
        $this->uploadFormFactory = $uploadFormFactory;
        $this->multiuploadFormFactory = $multiuploadFormFactory;
        $this->disableMultiupload = $disableMultiupload;
    }

    /**
     * @return AjaxUploadControl
     */
    public function create()
    {
        return new AjaxUploadControl($this->getMediaManager(), $this->getFormFactory());
    }

    protected function getMediaManager()
    {
        return $this->manager;
    }

    protected function getFormFactory()
    {
        if ($this->disableMultiupload) {
            return $this->uploadFormFactory;
        } else {
            return $this->multiuploadFormFactory;
        }
    }
}