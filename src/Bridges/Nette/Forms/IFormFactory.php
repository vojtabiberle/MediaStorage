<?php


namespace vojtabiberle\MediaStorage\Bridges\Nette\Forms;


use Nette\Application\UI\Form;

interface IFormFactory
{
    /**
     * @return Form
     */
    public function create();
}