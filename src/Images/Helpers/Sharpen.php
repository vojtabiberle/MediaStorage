<?php

namespace vojtabiberle\MediaStorage\Images\Helpers;

use vojtabiberle\MediaStorage\Images\IImage;

class Sharpen implements IHelper
{
    /**
     * @param IImage  $image
     * @param string $parameter
     */
    public function __invoke(IImage &$image, $parameter) {
        $image->sharpen();
    }
}