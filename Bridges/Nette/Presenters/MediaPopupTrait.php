<?php


namespace MediaStorage\Bridges\Nette\Presenters;


use vojtabiberle\MediaStorage\Bridges\Nette\Controls\AjaxUpload\AjaxUploadControl;
use vojtabiberle\MediaStorage\Bridges\Nette\Controls\AjaxUpload\AjaxUploadControlFactory;
use vojtabiberle\MediaStorage\Bridges\Nette\Controls\GridMediaView\GridMediaViewControl;
use vojtabiberle\MediaStorage\Bridges\Nette\Controls\GridMediaView\GridMediaViewControlFactory;
use vojtabiberle\MediaStorage\Bridges\Nette\Controls\Upload\UploadControlFactory;

trait MediaPopupTrait
{
    /** @var  GridMediaViewControlFactory @inject */
    public $gridMediaViewFactory;

    /** @var  UploadControlFactory @inject */
    public $uploadControlFactory;

    /** @var  AjaxUploadControlFactory @inject */
    public $ajaxUploadControlFactory;

    /**
     * @return AjaxUploadControl
     */
    public function createComponentAjaxUploadControl()
    {
        return $this->ajaxUploadControlFactory->create();
    }

    /**
     * @return GridMediaViewControl
     */
    public function createComponentGridMediaViewControl()
    {
        return $this->gridMediaViewFactory->create();
    }
}