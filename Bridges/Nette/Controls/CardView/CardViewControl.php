<?php

namespace vojtabiberle\MediaStorage\Bridges\Nette\Controls\CardView;

use Nette\Application\UI\Control;
use vojtabiberle\MediaStorage\Files\IFile;
use vojtabiberle\MediaStorage\Images\IImage;
use vojtabiberle\MediaStorage\IManager;

class CardViewControl extends Control
{
    /** @var  IManager */
    private $mediaManager;

    public function __construct(IManager $manager)
    {
        parent::__construct();
        $this->mediaManager = $manager;
    }

    public function render(IFile $file)
    {
        $this->template->setFile(__DIR__. DS . 'templates' . DS . 'card.latte');
        $this->template->mediaManager = $this->mediaManager;

        $this->template->file = $file;
        $this->template->render();
    }

    public function renderImage(IImage $image)
    {
        $this->template->setFile(__DIR__. DS . 'templates' . DS . 'file_card.latte');
        $this->template->mediaManager = $this->mediaManager;

        $this->template->file = $image;
        $this->template->render();
    }

    public function renderFile(IFile $file)
    {
        $this->template->setFile(__DIR__. DS . 'templates' . DS . 'image_card.latte');
        $this->template->mediaManager = $this->mediaManager;

        $this->template->file = $file;
        $this->template->render();
    }
}