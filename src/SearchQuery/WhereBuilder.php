<?php

namespace vojtabiberle\MediaStorage\SearchQuery;

use vojtabiberle\MediaStorage\Bridges\Nette\Model\MediaStorage;
use vojtabiberle\MediaStorage\FileFilter;
use vojtabiberle\MediaStorage\SearchQuery\Token\AbstractToken;
use vojtabiberle\MediaStorage\SearchQuery\Token\AndToken;
use vojtabiberle\MediaStorage\SearchQuery\Token\ExperienceToken;
use vojtabiberle\MediaStorage\SearchQuery\Token\ExtensionToken;
use vojtabiberle\MediaStorage\SearchQuery\Token\MinusToken;
use vojtabiberle\MediaStorage\SearchQuery\Token\NotToken;
use vojtabiberle\MediaStorage\SearchQuery\Token\NsToken;
use vojtabiberle\MediaStorage\SearchQuery\Token\OrToken;
use vojtabiberle\MediaStorage\SearchQuery\Token\TripToken;
use vojtabiberle\MediaStorage\SearchQuery\Token\WordToken;

class WhereBuilder
{
    private $stream;

    private $where = '';

    private $params = [];

    public function __construct($stream)
    {
        $this->stream = $stream;
    }

    private function getNextToken()
    {
        return array_shift($this->stream);
    }

    public function build()
    {
        $filter = FileFilter::create()->findAll();

        if (count($this->stream) == 0) {
            return $filter;
        }

        while(null !== ($token = $this->getNextToken()))
        {
            $this->parseToken($token);
        }


        return $filter->customWhere($this->where, $this->params);
    }

    private function parseToken(AbstractToken $token)
    {
        switch (true) {
            case $token instanceof WordToken:
            case $token instanceof TripToken:
            case $token instanceof ExperienceToken:
            case $token instanceof NsToken:
            case $token instanceof ExtensionToken:
                $parsed = $this->parseWordToken($token);
                $this->where .= $parsed['field'].' '.$parsed['condition'].' ? ';
                array_push($this->params, $parsed['value']);
                break;
            case $token instanceof MinusToken:
            case $token instanceof NotToken:
                $this->where .= 'AND ';
                $innerToken = $this->getNextToken();
                $parsed = $this->parseWordToken($innerToken);
                $this->where .= $parsed['field'].' NOT '.$parsed['condition'].' ? ';
                array_push($this->params, $parsed['value']);
                break;
            case $token instanceof AndToken:
                $this->where .= 'AND ';
                break;
            case $token instanceof OrToken:
                $this->where .= 'OR ';
                break;
        }
    }

    private function parseWordToken(AbstractToken $token)
    {
        $parsed = [];
        $value = $token->getValue();
        switch (true) {
            case $token instanceof WordToken:
                $parsed['field'] = MediaStorage::MEDIA_STORAGE_TABLE.'.name';
                $parsed['condition'] = 'LIKE';
                $parsed['value'] = '%'.$value.'%';
                break;
            case $token instanceof TripToken:
                $parsed['field'] = ':'.MediaStorage::MEDIA_USAGE_TABLE.'.namespace';
                $parsed['condition'] = 'LIKE';
                $parsed['value'] = '%trip/'.$value.'%';
                break;
            case $token instanceof ExperienceToken:
                $parsed['field'] = ':'.MediaStorage::MEDIA_USAGE_TABLE.'.namespace';
                $parsed['condition'] = 'LIKE';
                $parsed['value'] = '%experience/'.$value.'%';
                break;
            case $token instanceof NsToken:
                $parsed['field'] = ':'.MediaStorage::MEDIA_USAGE_TABLE.'.namespace';
                $parsed['condition'] = 'LIKE';
                $parsed['value'] = '%'.$value.'%';
                break;
            case $token instanceof ExtensionToken:
                $parsed['field'] = MediaStorage::MEDIA_STORAGE_TABLE.'.name';
                $parsed['condition'] = 'LIKE';
                $parsed['value'] = '%'.$value.'';
                break;
        }

        return $parsed;
    }
}