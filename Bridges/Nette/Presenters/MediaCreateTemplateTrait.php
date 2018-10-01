<?php


namespace MediaStorage\Bridges\Nette\Presenters;


use vojtabiberle\MediaStorage\IManager;

trait MediaCreateTemplateTrait
{
    /** @var IManager @inject*/
    public $mediaManager;

    public function createTemplate()
    {
        $template = parent::createTemplate();
        $template->mediaManager = $this->mediaManager;
        return $template;
    }

    /**
     * Formats layout template file names.
     * @return array
     */
    public function formatLayoutTemplateFiles()
    {
        $name = $this->getName();
        $presenter = substr($name, strrpos(':' . $name, ':'));
        $layout = $this->layout ? $this->layout : 'layout';
        $dir = dirname($this->getReflection()->getFileName());
        $dir = is_dir("$dir/templates") ? $dir : dirname($dir);
        $msDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
        $list = [
            "$dir/templates/$presenter/@$layout.latte",
            "$dir/templates/$presenter.@$layout.latte",
            $msDir.$presenter.DIRECTORY_SEPARATOR.'@'.$layout.'.latte',
            $msDir.$presenter.'.@'.$layout.'.latte'
        ];
        do {
            $list[] = "$dir/templates/@$layout.latte";
            $dir = dirname($dir);
        } while ($dir && ($name = substr($name, 0, strrpos($name, ':'))));
        return $list;
    }

    /**
     * Formats view template file names.
     * @return array
     */
    public function formatTemplateFiles()
    {
        $name = $this->getName();
        $presenter = substr($name, strrpos(':' . $name, ':'));
        $dir = dirname($this->getReflection()->getFileName());
        $dir = is_dir("$dir/templates") ? $dir : dirname($dir);
        $msDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
        return [
            "$dir/templates/$presenter/$this->view.latte",
            "$dir/templates/$presenter.$this->view.latte",
            $msDir.$presenter.DIRECTORY_SEPARATOR.$this->view.'.latte',
            $msDir.$presenter.'.'.$this->view.'.latte'
        ];
    }
}