<?php

namespace vojtabiberle\MediaStorage\MediaUsage;

class MediaUsage implements IMediaUsage
{
    /** @var  int */
    private $mediaId;

    /** @var  string */
    private $namespace;

    public function __construct($mediaId, $namespace)
    {
        $this->mediaId = $mediaId;
        $this->namespace = $namespace;
    }

    /**
     * @return int
     */
    public function getMediaId()
    {
        return $this->mediaId;
    }

    /**
     * @param $id
     * @return $this
     */
    public function setMediaId($id)
    {
        $this->mediaId = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param $namespace
     * @return $this
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }
}