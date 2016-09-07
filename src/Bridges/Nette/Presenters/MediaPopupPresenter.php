<?php

namespace vojtabiberle\MediaStorage\Bridges\Nette\Presenters;

use MediaStorage\Bridges\Nette\Presenters\MediaCreateTemplateTrait;
use MediaStorage\Bridges\Nette\Presenters\MediaPopupTrait;
use Nette\Application\UI\Presenter;

class MediaPopupPresenter extends Presenter
{
    use MediaPopupTrait;
    use MediaCreateTemplateTrait;

    public function renderGridPopup()
    {

    }
}