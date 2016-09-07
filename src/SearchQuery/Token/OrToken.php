<?php

namespace vojtabiberle\MediaStorage\SearchQuery\Token;

use vojtabiberle\MediaStorage\SearchQuery\SimpleQueryTokenizer;

class OrToken extends AbstractToken implements IConjuctionToken
{
    public function __construct($value = null)
    {
        parent::__construct(SimpleQueryTokenizer::OROP);
    }
}