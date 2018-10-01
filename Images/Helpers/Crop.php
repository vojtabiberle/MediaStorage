<?php

namespace vojtabiberle\MediaStorage\Images\Helpers;

use vojtabiberle\MediaStorage\Images\IImage;

class Crop implements IHelper
{
    /**
     * @param array $array
     * @return array
     */
    private function formatParameters(array $array) {
        return array_map(function ($value) {
            return trim($value);
        }, $array);
    }

    /**
     * @param IImage $image
     * @param string $parameter
     * @return mixed|void
     */
    public function __invoke(IImage &$image, $parameter) {
        call_user_func_array([$image, 'crop'], $this->formatParameters(explode(',', $parameter)));
    }
}