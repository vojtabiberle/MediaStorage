<?php

namespace vojtabiberle\MediaStorage\Bridges\Nette\Forms;

use Nette\Application\UI\Form;

class UploadFormFactory extends AbstractFormFactory
{
    /**
     * @return Form
     */
    public function create()
    {
        $this->form->addUpload('upload', $this->label);
        $this->form->addSubmit('submit', 'Submit');
        return $this->form;
    }
}