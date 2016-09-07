<?php

namespace vojtabiberle\MediaStorage\Bridges\Nette\Forms\Controls;

use Nette;
use vojtabiberle\MediaStorage\Exceptions\Exception;
use vojtabiberle\MediaStorage\FileFilter;
use vojtabiberle\MediaStorage\IManager;

class MultiSelectFileChoicer extends SingleSelectFileChoicer
{
    public function __construct(IManager $mediaManager, $namespace, $label = null, $withPrimary = true)
    {
        parent::__construct($mediaManager, $namespace, $label = null, $withPrimary);
        $this->fileFilter = FileFilter::create()->findByNamespace($this->namespace);
    }

    public function isSingle()
    {
        return false;
    }

    public static function register($controlName = 'addMultiSelectFileChoicer')
    {
        if (!is_string($controlName)) {
            throw new Exception(sprintf('Control name must be string, %s given', gettype($controlName)));
        }

        Nette\Object::extensionMethod('Nette\Forms\Container::' . $controlName,
            function ($form, $name, IManager $mediaManager, $namespace, $label = null, $withPrimary = true)
            {
                return $form[$name] = new \vojtabiberle\MediaStorage\Bridges\Nette\Forms\Controls\MultiSelectFileChoicer($mediaManager, $namespace, $label, $withPrimary);
            }
        );
    }
}