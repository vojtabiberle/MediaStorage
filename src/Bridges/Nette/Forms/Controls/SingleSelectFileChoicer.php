<?php

namespace vojtabiberle\MediaStorage\Bridges\Nette\Forms\Controls;

use Nette;
use Nette\Application\UI\Presenter;
use Nette\Forms\Controls\BaseControl;
use Nette\Utils\Html;
use vojtabiberle\MediaStorage\Bridges\IFormFieldFileChoicer;
use vojtabiberle\MediaStorage\Exceptions\Exception;
use vojtabiberle\MediaStorage\Exceptions\FileNotFoundException;
use vojtabiberle\MediaStorage\FileFilter;
use vojtabiberle\MediaStorage\Files\IFile;
use vojtabiberle\MediaStorage\IManager;

class SingleSelectFileChoicer extends BaseControl implements IFormFieldFileChoicer
{
    /** @var string $namespace */
    protected $namespace;

    /** @var IManager $mediaManager */
    protected $mediaManager;

    /** @var string */
    protected $gridImageSize = 'size-grid-thumbnail';

    protected $fileFilter;

    protected $withPrimary = false;

    protected $used = null;
    protected $removed = null;
    protected $primary = null;

    public function __construct(IManager $mediaManager, $namespace, $label = null, $withPrimary = false)
    {
        parent::__construct($label);
        $this->mediaManager = $mediaManager;
        $this->namespace = $namespace;
        $this->fileFilter = FileFilter::create()->getByNamespace($this->namespace);
        $this->withPrimary = $withPrimary;
    }

    /*=== GETTERS and SETTERS ===*/

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function setNamespace($ns)
    {
        $this->namespace = $ns;
    }

    public function getFiles()
    {
        /** Should we cached find result? */
        $files = $this->mediaManager->find($this->fileFilter);
        if ($files instanceof IFile) { //just single file
            return [$files];
        } else {
            return $files;
        }
    }

    public function getUsedIds()
    {
        return $this->used;
    }

    public function getRemovedIds()
    {
        return $this->removed;
    }

    public function getPrimaryId()
    {
        return $this->primary;
    }

    public function isSingle()
    {
        return true;
    }

    /*=== Public methods ===*/

    /**
     * pseudo HAML:
     * div.inline-file-choice
     *  hidden#files
     *  div.file | div.image
     *      img.src="image|icon"
     *      a href="image|file" "file name"
     *      input type=hidden name=`getHtmlName`-id[] value=UID
     *          OR
     *      input type=hidden name=`getHtmlName`-removed[] value=UID
     *
     * @return Html
     */
    public function getControl()
    {
        $holder = Html::el('div', ['class' => 'mediastorage inline-file-choice']);
        $holder->data('mediastorage-filechoicer-init', json_encode(['name' => $this->getHtmlName().$this->getHiddenUsedTail()]));

        $hidden_remove = $holder->create('input', ['type' => 'hidden']);
        $hidden_remove->name = $this->getHtmlName().$this->getHiddenRemovedTail();
        $hidden_remove->data('mediastorage-role', 'removed-template');

        if($this->withPrimary) {
            $hidden_primary = $holder->create('input', ['type' => 'hidden']);
            $hidden_primary->name = $this->getHtmlName().$this->getHiddenPrimaryTail();
            $hidden_primary->data('mediastorage-role', 'primary-uid');
        }

        $fileHolder = Html::el('div');

        /** @property Presenter $presenter */
        $presenter = $this->form->getPresenter();

        /**
         * @var string $key
         * @var IFile $file
         */
        $count = 0;
        try {
            $files = $this->getFiles();
            foreach($files as $key => $file) {
                $fh = clone $fileHolder;
                $fh->id = $file->getUID();
                $fh->data('original-title', $file->getName());
                $holder->addHtml($fh);
                $content = $fh->create('div', ['class' => 'content']);
                $image = $content->create('img');

                $footer = $fh->create('div', ['class' => 'footer']);
                $name = $footer->create('a');

                $hidden_added = $fh->create('input', ['type' => 'hidden']);
                $hidden_added->name = $this->getHtmlName().$this->getHiddenUsedTail();
                $hidden_added->value = $file->getUID();

                if ($file->isImage()) {
                    $fh->class = 'mediastorage-frame image';
                    $image->src = $presenter->link(":MediaStorage:Images:", ['name' => $file, 'namespace' => $this->namespace, 'size' => $this->gridImageSize]);
                    $name->href = $presenter->link(":MediaStorage:Images:", ['name' => $file]);
                } else {
                    $fh->class = 'mediastorage-frame file';
                    $image->src = $presenter->link(":MediaStorage:Icons:", ['name' => $file->getIconName(), 'size' => $this->gridImageSize]);
                    $name->href = $presenter->link(":MediaStorage:Files:", ['name' => $file]);
                }
                $name->setText($file->getName());

                $overlay = $fh->create('div', ['class' => 'overlay']);

                $remover = $overlay->create('a');
                //$remover->setText($this->translate('Remove'));
                $remover->href = '#remove';
                $remover->class = 'remover glyphicon glyphicon-trash';
                $remover->data('mediastorage-uid', $file->getUID());

                if($this->withPrimary){
                    if($file->isPrimary()) {
                        $unsetPrimary = $overlay->create('a');
                        $unsetPrimary->setText($this->translate('Remove primary'));
                        $unsetPrimary->href = '#unsetPrimary';
                        $unsetPrimary->class = 'unsetPrimary';
                        $unsetPrimary->data('mediastorage-uid', $file->getUID());
                        $hidden_primary->value = $file->getUID();
                    } else {
                        $setPrimary = $overlay->create('a');
                        $setPrimary->setText($this->translate('Set primary'));
                        $setPrimary->href = '#setPrimary';
                        $setPrimary->class = 'setPrimary';
                        $setPrimary->data('mediastorage-uid', $file->getUID());
                    }
                }

                $count++;
            }
        } catch(FileNotFoundException $e) {
            $div = $holder->create('div', ['class' => 'warning']);
            $div->setText($this->translate($e->getMessage()));
        }

        $popupAction = Html::el('a', ['class' => 'adder btn btn-info']);
        $popupAction->href = $presenter->link(':MediaStorage:MediaPopup:gridPopup');
        $popupAction->setText($this->translate('Select another file'));
        $holder->addHtml($popupAction);

        return $holder->addAttributes(parent::getControl()->attrs);
    }

    /**
     * Loads HTTP data.
     * @return void
     */
    public function loadHttpData()
    {
        $this->value = $this;

        $used = $this->getHttpData(Nette\Forms\Form::DATA_LINE, $this->getHiddenUsedTail());
        $this->used =  array_filter($used);
        $removed = $this->getHttpData(Nette\Forms\Form::DATA_LINE, $this->getHiddenRemovedTail());
        $this->removed = array_filter($removed);
        if($this->withPrimary) {
            $primary = $this->getHttpData(Nette\Forms\Form::DATA_LINE, $this->getHiddenPrimaryTail());
            if(!empty($primary)) {
                if (is_string($primary) && mb_strlen($primary) == 23) {
                    $this->primary = $primary;
                } else {
                    $this->primary = filter_var($primary, FILTER_VALIDATE_BOOLEAN);
                }
            }
        }
    }

    /*=== Private methods ===*/

    private function getHiddenUsedTail()
    {
        return '[used][]';
    }

    private function getHiddenRemovedTail()
    {
        return '[removed][]';
    }

    private function getHiddenPrimaryTail()
    {
        return '[primary]';
    }

    /*=== Static methods ===*/

    public static function register($controlName = 'addSingleSelectFileChoicer')
    {
        if (!is_string($controlName)) {
            throw new Exception(sprintf('Control name must be string, %s given', gettype($controlName)));
        }

        Nette\Object::extensionMethod('Nette\Forms\Container::' . $controlName,
            function ($form, $name, IManager $mediaManager, $namespace, $label = null)
            {
                return $form[$name] = new \vojtabiberle\MediaStorage\Bridges\Nette\Forms\Controls\SingleSelectFileChoicer($mediaManager, $namespace, $label);
            }
        );
    }
}