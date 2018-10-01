<?php


namespace vojtabiberle\MediaStorage\MediaUsage;


interface IMediaUsage
{
    public function getMediaId();
    public function setMediaId($id);

    public function getNamespace();
    public function setNamespace($namespace);
}