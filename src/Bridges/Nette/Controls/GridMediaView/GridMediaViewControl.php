<?php

namespace vojtabiberle\MediaStorage\Bridges\Nette\Controls\GridMediaView;

use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use vojtabiberle\MediaStorage\Bridges\Nette\Controls\CardView\CardViewControl;
use vojtabiberle\MediaStorage\Bridges\Nette\Forms\FilterFormFactory;
use vojtabiberle\MediaStorage\FileFilter;
use vojtabiberle\MediaStorage\IManager;
use vojtabiberle\MediaStorage\SearchQuery\SimpleQueryTokenizer;
use vojtabiberle\MediaStorage\SearchQuery\WhereBuilder;

class GridMediaViewControl extends Control
{
    /** @var  IManager */
    private $mediaManager;

    /** @var  FilterFormFactory */
    private $filterFormFactory;

    /** @var  CardViewControl */
    private $cardViewControl;

    private $currentFilter;

    public function __construct(IManager $manager, FilterFormFactory $filterFormFactory, CardViewControl $cardViewControl)
    {
        parent::__construct();
        $this->mediaManager = $manager;
        $this->filterFormFactory = $filterFormFactory;
        $this->cardViewControl = $cardViewControl;
        $this->currentFilter = FileFilter::create()->orderDESC();
    }

    public function render()
    {
        $this->template->setFile(__DIR__. DS . 'templates' . DS . 'grid.latte');
        $this->template->mediaManager = $this->mediaManager;

        try {
            $this->template->files = $this->mediaManager->find($this->currentFilter);
        } catch(\vojtabiberle\MediaStorage\Exceptions\FileNotFoundException $e) {
            $this->template->files = [];
        }

        $this->template->render();
    }

    public function handleDelete($uid)
    {
        $this->mediaManager->deleteById($uid);
        $this->redrawControl('gridMediaFiles');
    }

    public function createComponentFilterForm()
    {
        $form = $this->filterFormFactory->create();
        $form->onSuccess[] = [$this, 'handleSetFilter'];
        return $form;
    }

    /**
     * @return CardViewControl
     */
    public function createComponentCardView()
    {
        return $this->cardViewControl;
    }

    public function handleSetFilter(Form $form, $values)
    {
        if (isset($values['query'])) {
            $tokenizer = new SimpleQueryTokenizer();
            $stream = $tokenizer->parse($values['query']);
            $whereBuilder = new WhereBuilder($stream);
            $this->currentFilter = $whereBuilder->build();

            $this->redrawControl('gridMediaFiles');
        }
    }
}