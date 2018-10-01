<?php

namespace vojtabiberle\MediaStorage\Bridges\Nette\Presenters;

use MediaStorage\Bridges\Nette\Presenters\MediaPresenterTrait;
use Nette\Application\UI\Presenter;
use vojtabiberle\MediaStorage\Exceptions\Exception;

class MediaPresenter extends Presenter
{
    use MediaPresenterTrait;

    public function actionDelete($uid)
    {
        $response = new \stdClass();
        try {
            $response->success = $this->mediaManager->deleteById($uid);
        } catch (Exception $e) {
            $response->success = false;
        }

        if ($this->isAjax()) {
            $this->sendJson($response);
        } else {
            //todo: translations
            //note: better messages?
            if ($response->success) {
                $this->flashMessage('File was deleted.', 'success');
            } else {
                $this->flashMessage('Error!', 'error');
            }
        }
    }
}