<?php

namespace vojtabiberle\MediaStorage\Bridges\Nette\Presenters;

use Nette\Application\BadRequestException;
use Nette\Application\Responses\FileResponse;
use Nette\Application\UI\Presenter;
use vojtabiberle\MediaStorage\Exceptions\FileNotFoundException;
use vojtabiberle\MediaStorage\Images\IImage;
use vojtabiberle\MediaStorage\IManager;

class ImagesPresenter extends Presenter
{
    /** @var  IManager @inject */
    public $mediaManager;

    public function actionDefault($name, $namespace = null,  $size = null, $filters = null, $noimage = null)
    {
        try {
            /** @var IImage $image */
            $image = $this->mediaManager->publishFile($name, $namespace, $size, $filters, $noimage);
        } catch (FileNotFoundException $e) {
            throw new BadRequestException($e->getMessage(), 404, $e);
        }

        header('Content-Type: ' . $image->getContentType());
        $this->sendResponse(new FileResponse($image->getFullPath(), $image->getName(), $image->getContentType(), false));
    }
}