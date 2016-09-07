<?php

namespace vojtabiberle\MediaStorage\Bridges\Nette\Forms;

use Nette\Application\UI\Form;
use vojtabiberle\MediaStorage\Exceptions\RuntimeException;

abstract class AbstractFormFactory implements IFormFactory
{
    /** @var  Form */
    protected $form;

    /** @var  string */
    protected $label;

    public function __construct($form = 'Nette\Application\UI\Form', $label = 'Upload:')
    {
        if (is_object($form)) {
            $this->form = $form;
        } elseif (is_string($form) && class_exists($form)) {
            $this->form = new $form;
        } else {
            throw new RuntimeException('Can\'not instantiate Form object: '.$form);
        }

        $this->label = $label;
    }
}