<?php

namespace vojtabiberle\MediaStorage\Bridges\Nette\Forms;

use Nette\Application\UI\Form;
use Travelove\Application\UI\Bootstrap3Form;
use Travelove\Forms\Rendering\Bootstrap3FormRenderer;

class FilterFormFactory extends AbstractFormFactory
{

    public function __construct($form = 'Nette\Application\UI\Form', $label = 'Upload:')
    {
        parent::__construct($form, $label);
        if ($this->form instanceof Bootstrap3Form) {
            $this->form->setRenderer(new Bootstrap3FormRenderer('form-inline ajax'));
        }
    }

    /**
     * @return Form
     */
    public function create()
    {
        $this->form->addText('query', $this->label);
        $this->form->addSubmit('submit', 'Filter');
        return $this->form;
    }
}