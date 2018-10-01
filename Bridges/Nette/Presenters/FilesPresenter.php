<?php

namespace vojtabiberle\MediaStorage\Bridges\Nette\Presenters;

use Nette\Application\BadRequestException;
use Nette\Application\Responses\FileResponse;
use Nette\Application\UI\Presenter;
use vojtabiberle\MediaStorage\Exceptions\FileNotFoundException;
use vojtabiberle\MediaStorage\Files\IFile;
use vojtabiberle\MediaStorage\IManager;

class FilesPresenter extends Presenter
{
    /** @var  IManager @inject */
    public $mediaManager;

    public function actionDefault($name, $namespace = null)
    {
        try {
            /** @var IFile $image */
            $file = $this->mediaManager->publishFile($name);
        } catch (FileNotFoundException $e) {
            throw new BadRequestException($e->getMessage(), 404, $e);
        }

        header('Content-Type: ' . $file->getContentType());
        $this->sendResponse(new FileResponse($file->getFullPath(), $file->getName(), null, false));
    }
}