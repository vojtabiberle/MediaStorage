<?php

namespace vojtabiberle\MediaStorage\Bridges\Nette\Forms;

use Nette\Application\UI\Form;

class MultiuploadFormFactory extends AbstractFormFactory
{

    /**
     * @return Form
     */
    public function create()
    {
        $this->form->addMultiUpload('upload', $this->label);
        $this->form->addSubmit('submit', 'Submit');
        return $this->form;
    }
}