<?php

namespace vojtabiberle\MediaStorage\Bridges\Nette\Controls\Upload;

use MediaStorage\Exceptions\NoFileUploadedException;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Http\FileUpload;
use vojtabiberle\MediaStorage\Bridges\Nette\Forms\IFormFactory;
use vojtabiberle\MediaStorage\Exceptions\RuntimeException;
use vojtabiberle\MediaStorage\IManager;

class UploadControl extends Control
{
    /** @var  IFormFactory */
    private $uploadFormFactory;

    /** @var  IManager */
    protected $manager;

    public function __construct(IManager $manager, IFormFactory $formFactory)
    {
        parent::__construct();
        $this->uploadFormFactory = $formFactory;
        $this->manager = $manager;
    }

    public function render()
    {
        $this->template->setFile(__DIR__. DS . 'templates' . DS . 'upload.latte');
        //$this->template->mediaManager = $this->storageManager;
        $this->template->render();
    }

    public function handleUpload(Form $form, $values)
    {
        try {
            if (is_array($values['upload'])) {
                /** @var FileUpload $fileUpload */
                foreach ($values['upload'] as $fileUpload) {
                    $file = \vojtabiberle\MediaStorage\Bridges\Nette\Http\FileUpload::createFromNetteFileUpload($fileUpload);
                    $this->manager->save($file);
                    $this->presenter->flashMessage('File "'.$file->getName().'" was uploaded');
                }
            } else {
                throw new NoFileUploadedException('No files was uploaded');
            }
        } catch (RuntimeException $e) {
            $this->presenter->flashMessage($e->getMessage(), 'error');
        }
    }

    /**
     * @return Form
     */
    public function createComponentUploadForm()
    {
        $form = $this->uploadFormFactory->create();
        $form->onSuccess[] = [$this, 'handleUpload'];
        return $form;
    }
}