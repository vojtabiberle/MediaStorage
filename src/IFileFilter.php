<?php

namespace vojtabiberle\MediaStorage;

use vojtabiberle\MediaStorage\Files\IFile;
use vojtabiberle\MediaStorage\Images\IImage;

interface IFileFilter
{
    public function getFilterType();
    public function getFilterValue();

    /**
     * Indicates if filter perform GET operation
     *
     * @return bool
     */
    public function isGet();

    /**
     * Indicates if filter perform FIND operation
     *
     * @return bool
     */
    public function isFind();

    /**
     * @return self
     */
    public static function create();

    /**
     * @param array $pairs
     * @return self
     */
    public function setPairs($pairs = null);

    /**
     * @return array
     */
    public function getPairs();

    /**
     * @param $table
     * @param $field
     * @param $condition
     * @return self
     */
    public function addAdditionalCondition($table, $condition);

    /**
     * @param $parameters
     * @return self
     */
    public function setAdditionalConditions($conditions);

    /**
     * @return array
     */
    public function getAdditionalConditions();

    /**
     * Find all files
     *
     * @return self
     */
    public function findAll();

    /**
     * Find files by name
     *
     * @param $name
     * @return self
     */
    public function findByName($name);

    /**
     * Find all images
     *
     * @return self
     */
    public function findImages();

    /**
     * Find all files that's not images
     *
     * @return self
     */
    public function findNotImages();

    /**
     * Find all files in namespace
     *
     * @param $namespace
     * @return self
     */
    public function findByNamespace($namespace);

    /**
     * Get one file by ID
     *
     * @param $id
     * @return mixed
     */
    public function getbyId($id);

    /**
     * Get one file by name
     *
     * @param $name
     * @return self
     */
    public function getByName($name);

    /**
     * Get one file by namespace
     *
     * @param $namespace
     * @return self
     */
    public function getByNamespace($namespace);
}