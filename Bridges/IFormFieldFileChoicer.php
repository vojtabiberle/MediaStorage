<?php


namespace vojtabiberle\MediaStorage\Bridges;


interface IFormFieldFileChoicer
{
    /**
     * Return namespace of field
     *
     * @return string
     */
    public function getNamespace();

    /**
     * Can change namespace in case it will change (depends for example of new ID of entry)
     *
     * @param $ns string
     */
    public function setNamespace($ns);

    /**
     * Return added media IDs to namespace
     *
     * @return array of UIDs
     */
    public function getUsedIds();

    /**
     * Return removed media IDs to namespace
     *
     * @return array of UIDs
     */
    public function getRemovedIds();

    /**
     * Return primary ID for MultiSelectFileChoicer
     *
     * @return string UID
     */
    public function getPrimaryId();

    /**
     * Return if is SingleSelect or MultiSelect
     *
     * @return boolean
     */
    public function isSingle();
}