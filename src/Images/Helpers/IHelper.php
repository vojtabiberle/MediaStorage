<?php


namespace vojtabiberle\MediaStorage\Images\Helpers;

use vojtabiberle\MediaStorage\Images\IImage;

interface IHelper
{
    /**
     * @param IImage  $image
     * @param string $parameter
     * @return mixed
     */
    public function __invoke(IImage &$image, $parameter);
}