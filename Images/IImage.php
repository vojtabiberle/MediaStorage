<?php

namespace vojtabiberle\MediaStorage\Images;

use vojtabiberle\MediaStorage\Files\IFile;

interface IImage
    extends IFile
{
    /**
     * Resizes image.
     * @param  mixed  width in pixels or percent
     * @param  mixed  height in pixels or percent
     * @param  int    flags
     * @return self
     */
    public function resize($width, $height, $flag);

    /**
     * Crops image.
     * @param  mixed $left  x-offset in pixels or percent
     * @param  mixed $top y-offset in pixels or percent
     * @param  mixed $width width in pixels or percent
     * @param  mixed $height height in pixels or percent
     * @return self
     */
    public function crop($left, $top, $width, $height);

    /**
     * Sharpen image.
     * @return self
     */
    public function sharpen();

    /**
     * @param integer $quality
     * @return self
     */
    public function setQuality($quality);

    /**
     * Saves image to the file.
     *
     * @param  string $filename
     * @param  int    $quality 0..100 (for JPEG and PNG)
     * @param  string $type    image type
     * @return bool TRUE on success or FALSE on failure.
     */
    public function save($file = NULL, $quality = NULL, $type = NULL);
}