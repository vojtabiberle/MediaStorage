<?php

namespace vojtabiberle\MediaStorage\Images;

use vojtabiberle\MediaStorage\Exceptions\Exception;
use vojtabiberle\MediaStorage\Exceptions\FileNotFoundException;
use vojtabiberle\MediaStorage\Exceptions\InvalidArgumentException;
use vojtabiberle\MediaStorage\Exceptions\RuntimeException;
use vojtabiberle\MediaStorage\Files\File;

/**
 * This class is mainly based on Nette\Utils\Image class.
 * And all glory belongs to David Grudl - until I rewrite this class :-)
 *
 * I use Image from Nette, because main integration is for Nette
 * and I need this be done really quick. Sorry, for copying.
 *
 */
class Image extends File implements IImage
{
    /** {@link resize()} only shrinks images */
    const SHRINK_ONLY = 1;

    /** {@link resize()} will ignore aspect ratio */
    const STRETCH = 2;

    /** {@link resize()} fits in given area so its dimensions are less than or equal to the required dimensions */
    const FIT = 0;

    /** {@link resize()} fills given area so its dimensions are greater than or equal to the required dimensions */
    const FILL = 4;

    /** {@link resize()} fills given area exactly */
    const EXACT = 8;

    /** image types */
    const JPEG = IMAGETYPE_JPEG,
        PNG = IMAGETYPE_PNG,
        GIF = IMAGETYPE_GIF;

    private $image;

    private $quality;

    public function __construct($resource = null, $name = null, $full_path = null, $size = null, $content_type = null, $is_image = null)
    {
        if (!is_null($resource)) {
            $this->setImageResource($resource);
        }
        parent::__construct($resource, $name, $full_path, $size, $content_type, true);
    }

    /**
     * Creates blank image.
     * @param  int
     * @param  int
     * @param  array
     * @return self
     */
    public static function fromBlank($width, $height, $color = NULL)
    {
        if (!extension_loaded('gd')) {
            throw new Exception('PHP extension GD is not loaded.');
        }

        $width = (int) $width;
        $height = (int) $height;
        if ($width < 1 || $height < 1) {
            throw new InvalidArgumentException('Image width and height must be greater than zero.');
        }

        $image = imagecreatetruecolor($width, $height);
        if (is_array($color)) {
            $color += ['alpha' => 0];
            $color = imagecolorresolvealpha($image, $color['red'], $color['green'], $color['blue'], $color['alpha']);
            imagealphablending($image, FALSE);
            imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, $color);
            imagealphablending($image, TRUE);
        }
        return new static($image);
    }

    /**
     * Opens image from file.
     * @param  string
     * @param  mixed  detected image format
     * @throws Exception if gd extension is not loaded
     * @throws FileNotFoundException if file not found or file type is not known
     * @return self
     */
    public static function fromFile($file, &$format = NULL)
    {
        if (!extension_loaded('gd')) {
            throw new Exception('PHP extension GD is not loaded.');
        }

        $parts = explode(DIRECTORY_SEPARATOR, $file);
        $name = array_pop($parts);

        static $funcs = [
            self::JPEG => 'imagecreatefromjpeg',
            self::PNG => 'imagecreatefrompng',
            self::GIF => 'imagecreatefromgif',
        ];
        $info = @getimagesize($file); // @ - files smaller than 12 bytes causes read error
        $format = $info[2];

        if (!isset($funcs[$format])) {
            throw new FileNotFoundException(is_file($file) ? "Unknown type of file '$file'." : "File '$file' not found.");
        }
        return new static(call_user_func_array($funcs[$format], [$file]), $name, $file);
    }

    /**
     * Returns image width.
     * @return int
     */
    public function getWidth()
    {
        return imagesx($this->getImageResource());
    }


    /**
     * Returns image height.
     * @return int
     */
    public function getHeight()
    {
        return imagesy($this->getImageResource());
    }

    /**
     * Returns image GD resource.
     * @return resource
     */
    public function getImageResource()
    {
        if (is_null($this->image) && !is_null($this->full_path)) {
            $this->image = self::createResource($this->full_path);
        }

        if (is_resource($this->image) && get_resource_type($this->image) === 'gd') {
            imagesavealpha($this->image, true);
        } else {
            throw new \InvalidArgumentException('Image is not valid.');
        }

        return $this->image;
    }

    /**
     * Sets image resource.
     * @param  resource
     * @return self
     */
    protected function setImageResource($image)
    {
        if (!is_resource($image) || get_resource_type($image) !== 'gd') {
            throw new \InvalidArgumentException('Image is not valid.');
        }
        $this->image = $image;
        return $this;
    }

    private static function createResource($file)
    {
        if (!extension_loaded('gd')) {
            throw new Exception('PHP extension GD is not loaded.');
        }

        static $funcs = [
            self::JPEG => 'imagecreatefromjpeg',
            self::PNG => 'imagecreatefrompng',
            self::GIF => 'imagecreatefromgif',
        ];
        $info = @getimagesize($file); // @ - files smaller than 12 bytes causes read error
        $format = $info[2];

        if (!isset($funcs[$format])) {
            throw new RuntimeException(is_file($file) ? "Unknown type of file '$file'." : "File '$file' not found.");
        }
        return call_user_func_array($funcs[$format], [$file]);
    }

    /**
     * Resizes image.
     * @param  mixed  width in pixels or percent
     * @param  mixed  height in pixels or percent
     * @param  int    flags
     * @return self
     */
    public function resize($width, $height, $flags = self::FIT)
    {
        if ($flags & self::EXACT) {
            return $this->resize($width, $height, self::FILL)->crop('50%', '50%', $width, $height);
        }

        list($newWidth, $newHeight) = static::calculateSize($this->getWidth(), $this->getHeight(), $width, $height, $flags);

        if ($newWidth !== $this->getWidth() || $newHeight !== $this->getHeight()) { // resize
            $newImage = static::fromBlank($newWidth, $newHeight, self::RGB(0, 0, 0, 127))->getImageResource();
            imagecopyresampled(
                $newImage, $this->getImageResource(),
                0, 0, 0, 0,
                $newWidth, $newHeight, $this->getWidth(), $this->getHeight()
            );
            $this->setImageResource($newImage);
        }

        if ($width < 0 || $height < 0) { // flip is processed in two steps for better quality
            $newImage = static::fromBlank($newWidth, $newHeight, self::RGB(0, 0, 0, 127))->getImageResource();
            imagecopyresampled(
                $newImage, $this->getImageResource(),
                0, 0, $width < 0 ? $newWidth - 1 : 0, $height < 0 ? $newHeight - 1 : 0,
                $newWidth, $newHeight, $width < 0 ? -$newWidth : $newWidth, $height < 0 ? -$newHeight : $newHeight
            );
            $this->setImageResource($newImage);
        }
        return $this;
    }

    /**
     * Crops image.
     * @param  mixed $left x-offset in pixels or percent
     * @param  mixed $top y-offset in pixels or percent
     * @param  mixed $width width in pixels or percent
     * @param  mixed $height height in pixels or percent
     * @return self
     */
    public function crop($left, $top, $width, $height)
    {
        list($left, $top, $width, $height) = static::calculateCutout($this->getWidth(), $this->getHeight(), $left, $top, $width, $height);
        $newImage = static::fromBlank($width, $height, self::RGB(0, 0, 0, 127))->getImageResource();
        imagecopy($newImage, $this->getImageResource(), 0, 0, $left, $top, $width, $height);
        $this->setImageResource($newImage);
        return $this;
    }

    /**
     * Sharpen image.
     * @return self
     */
    public function sharpen()
    {
        imageconvolution($this->getImageResource(), [ // my magic numbers ;)
            [-1, -1, -1],
            [-1, 24, -1],
            [-1, -1, -1],
        ], 16, 0);
        return $this;
    }

    /**
     * @param integer $quality
     * @return self
     */
    public function setQuality($quality)
    {
        $this->quality = $quality;
    }

    /**
     * Saves image to the file.
     *
     * @param  string $filename
     * @param  int $quality 0..100 (for JPEG and PNG)
     * @param  string $type image type
     * @return bool TRUE on success or FALSE on failure.
     */
    public function save($file = NULL, $quality = NULL, $type = NULL)
    {
        if (!is_null($file)) {
            $parts = explode(DIRECTORY_SEPARATOR, $file);
            $name = array_pop($parts);
            $this->name = $name;
            $this->full_path = $file;
        }

        if (is_null($quality) && !is_null($this->quality)) {
            $quality = $this->quality;
        }

        if ($type === NULL) {
            switch (strtolower($ext = pathinfo($file, PATHINFO_EXTENSION))) {
                case 'jpg':
                case 'jpeg':
                    $type = self::JPEG;
                    break;
                case 'png':
                    $type = self::PNG;
                    break;
                case 'gif':
                    $type = self::GIF;
                    break;
                default:
                    throw new InvalidArgumentException("Unsupported file extension '$ext'.");
            }
        }

        switch ($type) {
            case self::JPEG:
                if ($file === null) {
                    $quality = null;
                } else {
                    $quality = $quality === NULL ? 85 : max(0, min(100, (int)$quality));
                }
                return imagejpeg($this->getImageResource(), $file, $quality);

            case self::PNG:
                if ($file === null) {
                    $quality = null;
                } else {
                    $quality = $quality === NULL ? 9 : max(0, min(9, (int)$quality));
                }
                return imagepng($this->getImageResource(), $file, $quality);

            case self::GIF:
                return imagegif($this->getImageResource(), $file);

            default:
                throw new InvalidArgumentException("Unsupported image type '$type'.");
        }
    }

    public function getImageType()
    {
        switch (strtolower($ext = pathinfo($this->full_path, PATHINFO_EXTENSION))) {
            case 'jpg':
            case 'jpeg':
                $type = self::JPEG;
                break;
            case 'png':
                $type = self::PNG;
                break;
            case 'gif':
                $type = self::GIF;
                break;
            default:
                throw new InvalidArgumentException("Unsupported file extension '$ext'.");
        }
        return $type;
    }

    /**
     * Calculates dimensions of resized image.
     * @param  mixed  source width
     * @param  mixed  source height
     * @param  mixed  width in pixels or percent
     * @param  mixed  height in pixels or percent
     * @param  int    flags
     * @return array
     */
    public static function calculateSize($srcWidth, $srcHeight, $newWidth, $newHeight, $flags = self::FIT)
    {
        if (substr($newWidth, -1) === '%') {
            $newWidth = round($srcWidth / 100 * abs($newWidth));
            $percents = TRUE;
        } else {
            $newWidth = (int) abs($newWidth);
        }

        if (substr($newHeight, -1) === '%') {
            $newHeight = round($srcHeight / 100 * abs($newHeight));
            $flags |= empty($percents) ? 0 : self::STRETCH;
        } else {
            $newHeight = (int) abs($newHeight);
        }

        if ($flags & self::STRETCH) { // non-proportional
            if (empty($newWidth) || empty($newHeight)) {
                throw new InvalidArgumentException('For stretching must be both width and height specified.');
            }

            if ($flags & self::SHRINK_ONLY) {
                $newWidth = round($srcWidth * min(1, $newWidth / $srcWidth));
                $newHeight = round($srcHeight * min(1, $newHeight / $srcHeight));
            }

        } else {  // proportional
            if (empty($newWidth) && empty($newHeight)) {
                throw new InvalidArgumentException('At least width or height must be specified.');
            }

            $scale = [];
            if ($newWidth > 0) { // fit width
                $scale[] = $newWidth / $srcWidth;
            }

            if ($newHeight > 0) { // fit height
                $scale[] = $newHeight / $srcHeight;
            }

            if ($flags & self::FILL) {
                $scale = [max($scale)];
            }

            if ($flags & self::SHRINK_ONLY) {
                $scale[] = 1;
            }

            $scale = min($scale);
            $newWidth = round($srcWidth * $scale);
            $newHeight = round($srcHeight * $scale);
        }

        return [max((int) $newWidth, 1), max((int) $newHeight, 1)];
    }

    /**
     * Calculates dimensions of cutout in image.
     * @param  mixed  source width
     * @param  mixed  source height
     * @param  mixed  x-offset in pixels or percent
     * @param  mixed  y-offset in pixels or percent
     * @param  mixed  width in pixels or percent
     * @param  mixed  height in pixels or percent
     * @return array
     */
    public static function calculateCutout($srcWidth, $srcHeight, $left, $top, $newWidth, $newHeight)
    {
        if (substr($newWidth, -1) === '%') {
            $newWidth = round($srcWidth / 100 * $newWidth);
        }
        if (substr($newHeight, -1) === '%') {
            $newHeight = round($srcHeight / 100 * $newHeight);
        }
        if (substr($left, -1) === '%') {
            $left = round(($srcWidth - $newWidth) / 100 * $left);
        }
        if (substr($top, -1) === '%') {
            $top = round(($srcHeight - $newHeight) / 100 * $top);
        }
        if ($left < 0) {
            $newWidth += $left;
            $left = 0;
        }
        if ($top < 0) {
            $newHeight += $top;
            $top = 0;
        }
        $newWidth = min((int) $newWidth, $srcWidth - $left);
        $newHeight = min((int) $newHeight, $srcHeight - $top);
        return [$left, $top, $newWidth, $newHeight];
    }

    /**
     * Returns RGB color.
     * @param  int  red 0..255
     * @param  int  green 0..255
     * @param  int  blue 0..255
     * @param  int  transparency 0..127
     * @return array
     */
    public static function rgb($red, $green, $blue, $transparency = 0)
    {
        return [
            'red' => max(0, min(255, (int) $red)),
            'green' => max(0, min(255, (int) $green)),
            'blue' => max(0, min(255, (int) $blue)),
            'alpha' => max(0, min(127, (int) $transparency)),
        ];
    }

    public function __clone()
    {
        parent::__clone();
        /*ob_start();
        imagegd2($this->getImageResource());
        $this->setImageResource(imagecreatefromstring(ob_get_clean()));
        return;*/
        $image = $this->getImageResource();
        $w = imagesx($image);
        $h = imagesy($image);
        $trans = imagecolortransparent($image);

        if (imageistruecolor($image)) {
            $clone = imagecreatetruecolor($w, $h);
            imagealphablending($clone, false);
            imagesavealpha($clone, true);
        } else {
            $clone = imagecreate($w, $h);

            if($trans >= 0) {
                $rgb = imagecolorsforindex($image, $trans);

                imagesavealpha($clone, true);
                $trans_index = imagecolorallocatealpha($clone, $rgb['red'], $rgb['green'], $rgb['blue'], $rgb['alpha']);
                imagefill($clone, 0, 0, $trans_index);
            }
        }

        imagecopy($clone, $image, 0, 0, 0, 0, $w, $h);

        $this->setImageResource($clone);
    }
}