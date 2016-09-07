<?php

namespace MediaStorage\Images\Helpers;

use vojtabiberle\MediaStorage\Images\Helpers\IHelper;
use vojtabiberle\MediaStorage\Images\IImage;

class Resize implements IHelper
{

    /**
     * Example 1: 800x600-fit
     *
     * Example 2:
     [
      'width' => 800,
      'height' => 600,
      'flag' => 'fit'
     ]
     *
     * @param $parameters
     */
    private function formatParameters($parameters)
    {
        if (is_string($parameters)) {
            list($size, $flag) = explode('-', $parameters);
            list($width, $height) = explode('x', $size);
            return [$width, $height, $flag];
        }

        if (is_array($parameters)) {
            if (isset($parameters['width'], $parameters['height'], $parameters['flag'])) {
                return [$parameters['width'], $parameters['height'], $parameters['flag']];
            }
        }
    }

    /**
     * @param IImage $image
     * @param string $parameter
     * @return mixed
     */
    public function __invoke(IImage &$image, $parameter)
    {
        list($width, $height, $flag) = $this->formatParameters($parameter);
        $image->resize($width, $height, $flag);
    }
}