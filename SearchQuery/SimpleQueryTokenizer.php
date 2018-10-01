<?php

namespace vojtabiberle\MediaStorage\SearchQuery;

use vojtabiberle\MediaStorage\SearchQuery\Token\AbstractToken;
use vojtabiberle\MediaStorage\SearchQuery\Token\AndToken;
use vojtabiberle\MediaStorage\SearchQuery\Token\ExperienceToken;
use vojtabiberle\MediaStorage\SearchQuery\Token\ExtensionToken;
use vojtabiberle\MediaStorage\SearchQuery\Token\IConjuctionToken;
use vojtabiberle\MediaStorage\SearchQuery\Token\IlegalToken;
use vojtabiberle\MediaStorage\SearchQuery\Token\MinusToken;
use vojtabiberle\MediaStorage\SearchQuery\Token\NotToken;
use vojtabiberle\MediaStorage\SearchQuery\Token\NsToken;
use vojtabiberle\MediaStorage\SearchQuery\Token\OrToken;
use vojtabiberle\MediaStorage\SearchQuery\Token\TripToken;
use vojtabiberle\MediaStorage\SearchQuery\Token\WordToken;

class SimpleQueryTokenizer
{
    const ANDOP = 'AND';
    const OROP = 'OR';
    const NOTOP = 'NOT';
    const MINUS = 'MINUS';

    const WORD = 'word';
    const TRIP = 'trip';
    const EXPERIENCE = 'experience';
    const NS = 'namespace';
    const EXTENSION = 'extension';

    const ILEGAL = 'ilegal';

    private $defaultConjunction = self::ANDOP;

    private $input = [];
    private $stream = [];

    private $lastToken;
    private $currentToken;

    private $regEx = [
        self::ANDOP => '#^(?<value>AND)$#i',
        self::OROP => '#^(?<value>OR)$#i',
        self::NOTOP => '#^(?<value>NOT)$#i',
        self::MINUS => '#^(?<value>-)(?<inner>.+)$#i',
        self::WORD => '#^(?<value>[\w]+)$#i',
        self::TRIP => '#^trip:(?<value>[\d]+)$#i',
        self::EXPERIENCE => '#^experience:(?<value>[\d]+)$#i',
        self::NS => '#^(ns|namespace):(?<value>[\w]+)$#i',
        self::EXTENSION => '#^(ext|extension):(\*\.)?(?<value>[\w\.]+)$#i',
        self::ILEGAL => '#^(?<value>.*)$#', //must be last!
    ];

    public function parse($query)
    {
        $this->input = preg_split('#[\s]+#', $query, -1, PREG_SPLIT_NO_EMPTY);
        foreach ($this->input as $preToken) {
            $this->parsePreToken($preToken);
        }

        return $this->stream;
    }

    private function parsePreToken($preToken)
    {
        $token = null;
        $matches = [];
        foreach ($this->regEx as $tokenName => $rgx) {
            if (preg_match($rgx, $preToken, $matches))
            {
                $token =  $this->createToken($tokenName, $matches['value']);
                break;
            }
        }

        if (is_null($token)) {
            $token = $this->createToken(self::ILEGAL, $preToken);
        } else {
            if (array_key_exists('inner', $matches)) {
                $this->addToken($token);

                $tokenizer = new self();
                $stream = $tokenizer->parse($matches['inner']);
                $this->addTokens($stream);
            } else {
                $this->addToken($token);
            }
        }


        return $this->currentToken;
    }

    private function addTokens($tokens)
    {
        if (is_array($tokens) || $tokens instanceof \Traversable)
        {
            foreach($tokens as $token) {
                $this->addToken($token);
            }
        } elseif ($tokens instanceof AbstractToken) {
            $this->addToken($tokens);
        }
    }

    private function addToken(AbstractToken $token)
    {
        if(!is_null($this->currentToken)) {
            $this->lastToken = $this->currentToken;
        }
        $this->currentToken = $token;
        $this->addConjuctionToken();
        $this->stream[] = $this->currentToken;
    }

    private function addConjuctionToken()
    {
        /** We add conjunction into stream */
        if (!is_null($this->lastToken)) {
            if ( !($this->currentToken instanceof IConjuctionToken) && !($this->lastToken instanceof IConjuctionToken))
            {
                $this->stream[] = $this->createToken($this->defaultConjunction);
            }
        }
    }

    private function createToken($name, $value = null)
    {
        switch ($name) {
            case self::ANDOP:
                return new AndToken();
            case self::OROP:
                return new OrToken();
            case self::MINUS:
                return new MinusToken();
            case self::NOTOP:
                return new NotToken();
            case self::WORD:
                return new WordToken($value);
            case self::TRIP:
                return new TripToken($value);
            case self::EXPERIENCE:
                return new ExperienceToken($value);
            case self::NS:
                return new NsToken($value);
            case self::EXTENSION:
                return new ExtensionToken($value);
            default:
                return new IlegalToken();
        }
    }
}