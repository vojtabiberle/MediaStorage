<?php

namespace vojtabiberle\MediaStorage\Bridges\Nette\Presenters;

use Nette\Application\BadRequestException;
use Nette\Application\Responses\FileResponse;
use Nette\Application\UI\Presenter;
use vojtabiberle\MediaStorage\Bridges\Nette\Application\Responses\FileContentResponse;
use vojtabiberle\MediaStorage\Exceptions\FileNotFoundException;
use vojtabiberle\MediaStorage\Images\IImage;
use vojtabiberle\MediaStorage\IManager;

class IconsPresenter extends Presenter
{
    /** @var  IManager @inject */
    public $mediaManager;

    public function actionDefault($name, $size = null)
    {
        try {
            /** @var IImage $image */
            $image = $this->mediaManager->publishIcon($name, $size);
        } catch (FileNotFoundException $e) {
            throw new BadRequestException($e->getMessage(), 404, $e);
        }

        header('Content-Type: ' . $image->getContentType());
        $this->sendResponse(new FileContentResponse($image->save(null, null, $image->getImageType()), $image->getName(), $image->getContentType(), false));
    }
}