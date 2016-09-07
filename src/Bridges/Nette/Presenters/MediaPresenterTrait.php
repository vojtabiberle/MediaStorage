<?php


namespace MediaStorage\Bridges\Nette\Presenters;


use Nette\Application\UI\Form;
use vojtabiberle\MediaStorage\Bridges\Nette\Controls\AjaxUpload\AjaxUploadControl;
use vojtabiberle\MediaStorage\Bridges\Nette\Controls\AjaxUpload\AjaxUploadControlFactory;
use vojtabiberle\MediaStorage\Bridges\Nette\Controls\GridMediaView\GridMediaViewControl;
use vojtabiberle\MediaStorage\Bridges\Nette\Controls\GridMediaView\GridMediaViewControlFactory;
use vojtabiberle\MediaStorage\Bridges\Nette\Controls\Upload\UploadControl;
use vojtabiberle\MediaStorage\Bridges\Nette\Controls\Upload\UploadControlFactory;
use vojtabiberle\MediaStorage\Bridges\Nette\Forms\Controls\MultiSelectFileChoicer;
use vojtabiberle\MediaStorage\Bridges\Nette\Forms\Controls\SingleSelectFileChoicer;
use vojtabiberle\MediaStorage\IManager;

trait MediaPresenterTrait
{
    use MediaPopupTrait;
    use MediaCreateTemplateTrait;

    public function actionDefault()
    {
        $this->redirect('grid');
    }

    public function renderGrid()
    {

    }

    public function actionPopupSingle()
    {

    }

    public function actionShowSingle()
    {

    }

    public function actionPopupMulti()
    {

    }

    public function actionShowMulti()
    {

    }

    public function actionShowMultiPrimary()
    {

    }

    public function renderUpload()
    {

    }

    public function renderNoimage()
    {

    }

    /**
     * @return UploadControl
     */
    public function createComponentUploadControl()
    {
        return $this->uploadControlFactory->create();
    }

    /**
     * @return Form
     */
    public function createComponentPopupSingleTestForm()
    {
        $form = new Form();
        $form->addText('description', 'Description:');
        $form->addSingleSelectFileChoicer(
            'singleFileChoicer',
            $this->mediaManager,
            'mediastorage/test/single',
            'Selected file:'
        );
        $form->addSubmit('submit', 'Send');

        $manager = $this->mediaManager;
        $form->onSuccess[] = function(Form $form, $values) use ($manager) {
            /** @var SingleSelectFileChoicer $choicer */
            $choicer = $form['singleFileChoicer'];
            $manager->saveUsages($choicer->getNamespace(), $choicer->getUsedIds(), $choicer->getRemovedIds());
        };

        return $form;
    }

    /**
     * @return Form
     */
    public function createComponentPopupMultiTestForm()
    {
        $form = new Form();
        $form->addText('description', 'Description:');
        $form->addMultiSelectFileChoicer(
            'multipleFileChoicer',
            $this->mediaManager,
            'mediastorage/test/multi',
            'Choice file:',
            true //with primary media capability
        );
        $form->addSubmit('submit', 'Send');

        $manager = $this->mediaManager;
        $form->onSuccess[] = function(Form $form, $values) use ($manager) {
            /** @var MultiSelectFileChoicer $choicer */
            $choicer = $form['multipleFileChoicer'];
            $manager->saveUsages($choicer->getNamespace(), $choicer->getUsedIds(), $choicer->getRemovedIds());
            $manager->setPrimary('mediastorage/test/multi', $choicer->getPrimaryId());

        };

        return $form;
    }
}