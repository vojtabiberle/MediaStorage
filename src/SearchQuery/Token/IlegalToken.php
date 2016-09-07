<?php

namespace vojtabiberle\MediaStorage\SearchQuery\Token;

use vojtabiberle\MediaStorage\SearchQuery\SimpleQueryTokenizer;

class IlegalToken extends AbstractToken
{
    public function __construct($value = null)
    {
        parent::__construct(SimpleQueryTokenizer::ILEGAL);
    }
}