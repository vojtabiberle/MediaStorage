<?php

namespace vojtabiberle\MediaStorage;

use vojtabiberle\MediaStorage\Files\IFile;
use vojtabiberle\MediaStorage\Images\IImage;

class FileFilter implements IFileFilter
{
    const FILTER_FIND_ALL = 'all';
    const FILTER_FIND_BY_NAME = 'name';
    const FILTER_FIND_CONTENT_TYPE = 'content_type';
    const FILTER_FIND_IMAGES = 'is_images';
    const FILTER_FIND_NOT_IMAGES = 'not_images';
    const FILTER_FIND_NAMESPACE = 'namespace';
    const FILTER_GET_ID = 'get_id';
    const FILTER_GET_NAME = 'get_name';
    const FILTER_GET_NAMESPACE = 'get_namespace';
    const FILTER_CUSTOM_WHERE = 'custom_where';
    const FILTER_ORDER_DESC = 'order_desc';

    /**
     * @var bool $getFilter Flag if filter perform GET or FIND operation
     */
    private $getFilter = false;

    private $pairs = false;

    private $additionalConditions = [];

    private $current_filter_type;
    private $current_filter_value;

    public function __construct()
    {
        $this->findAll(); //default behavior
    }

    /**
     * @return self
     */
    public static function create()
    {
        return new self();
    }

    public function getFilterType()
    {
        return $this->current_filter_type;
    }

    public function getFilterValue()
    {
        return $this->current_filter_value;
    }

    public function isGet()
    {
        return $this->getFilter;
    }

    public function isFind()
    {
        return !$this->getFilter;
    }

    /**
     * @return self
     */
    public function setPairs($pairs = null)
    {
        if (is_array($pairs)) {
            $this->pairs = $pairs;
            return $this;
        } else {
            throw new \InvalidArgumentException('Parameter must by array.');
        }
    }

    /**
     * @return array
     */
    public function getPairs()
    {
        return $this->pairs;
    }

    public function addAdditionalCondition($table, $condition)
    {
        $this->additionalConditions[$table][] = $condition;
        return $this;
    }

    public function setAdditionalConditions($conditions)
    {
        $this->additionalConditions = $conditions;
        return $this;
    }

    public function getAdditionalConditions()
    {
        return $this->additionalConditions;
    }

    public function customWhere($where, $params)
    {
        $this->getFilter = false;
        $this->current_filter_type = self::FILTER_CUSTOM_WHERE;
        $this->current_filter_value = ['where' => $where, 'params' => $params];
        return $this;
    }

    public function orderDESC()
    {
        $this->getFilter = false;
        $this->current_filter_type = self::FILTER_ORDER_DESC;
        return $this;
    }

    /**
     * Find all files
     *
     * @return self
     */
    public function findAll()
    {
        $this->getFilter = false;
        $this->current_filter_type = self::FILTER_FIND_ALL;
        $this->current_filter_value = null;
        return $this;
    }

    /**
     * Only alias for findAll
     * @return self
     */
    public function clear()
    {
        return $this->findAll();
    }

    /**
     * Find files by name
     *
     * @param $name
     * @return self
     */
    public function findByName($name)
    {
        $this->clear();
        $this->current_filter_type = self::FILTER_FIND_BY_NAME;
        $this->current_filter_value = $name;
        return $this;
    }

    /**
     * Find all images
     *
     * @return self
     */
    public function findImages()
    {
        $this->clear();
        $this->current_filter_type = self::FILTER_FIND_IMAGES;
        return $this;
    }

    /**
     * Find all files that's not images
     *
     * @return self
     */
    public function findNotImages()
    {
        $this->clear();
        $this->current_filter_type = self::FILTER_FIND_NOT_IMAGES;
        return $this;
    }

    /**
     * Find all files in namespace
     *
     * @param $namespace
     * @return self
     */
    public function findByNamespace($namespace)
    {
        $this->clear();
        $this->current_filter_type = self::FILTER_FIND_NAMESPACE;
        $this->current_filter_value = $namespace;
        return $this;
    }

    public function getbyId($id)
    {
        $this->clear();
        $this->current_filter_type = self::FILTER_GET_ID;
        $this->current_filter_value = $id;
        return $this;
    }

    /**
     * Get one file by name
     *
     * @param $name
     * @return self
     */
    public function getByName($name)
    {
        $this->clear();
        $this->getFilter = true;
        $this->current_filter_type = self::FILTER_GET_NAME;
        $this->current_filter_value = $name;
        return $this;
    }

    /**
     * Get one file by namespace
     *
     * @param $namespace
     * @return self
     */
    public function getByNamespace($namespace)
    {
        $this->clear();
        $this->getFilter = true;
        $this->current_filter_type = self::FILTER_GET_NAMESPACE;
        $this->current_filter_value = $namespace;
        return $this;
    }
}