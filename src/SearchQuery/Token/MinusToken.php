<?php

namespace vojtabiberle\MediaStorage\SearchQuery\Token;


use vojtabiberle\MediaStorage\SearchQuery\SimpleQueryTokenizer;

class MinusToken extends AbstractToken implements IConjuctionToken
{
    public function __construct($value = null)
    {
        parent::__construct(SimpleQueryTokenizer::MINUS);
    }
}