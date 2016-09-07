<?php

namespace vojtabiberle\MediaStorage\Bridges\Nette\Controls\GridMediaView;

use Nette\Application\UI\PresenterComponent;
use vojtabiberle\MediaStorage\Bridges\Nette\Controls\CardView\CardViewControl;
use vojtabiberle\MediaStorage\Bridges\Nette\Forms\FilterFormFactory;
use vojtabiberle\MediaStorage\IManager;

class GridMediaViewControlFactory
{
    /** @var  IManager */
    private $manager;

    /** @var  FilterFormFactory */
    private $filterFormFactory;

    /** @var  CardViewControl */
    private $cardViewControl;

    public function __construct(IManager $manager, FilterFormFactory $filterFormFactory, CardViewControl $cardViewControl)
    {
        $this->manager = $manager;
        $this->filterFormFactory = $filterFormFactory;
        $this->cardViewControl = $cardViewControl;
    }

    public function create()
    {
        return new GridMediaViewControl($this->manager, $this->filterFormFactory, $this->cardViewControl);
    }
}