<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;
use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\FontInterface;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\Color;

/**
 * Image handler service
 *
 * Use {@link Imagaine} as image manipulation library
 *
 * Use cases:
 *
 * - Watermark
 * ```
 *  // Use specific watermark
 *  Pi::service('image')->watermark(
 *      <path/to/source/image>,
 *      <path/to/saved/image>
 *      <path/to/watermark/image>,
 *      <top-left|bottom-right|array(<x>, <y>)>
 *  );
 *
 *  // Use system watermark
 *  Pi::service('image')->watermark(
 *      <path/to/source/image>,
 *      <path/to/saved/image>,
 *      '',
 *      <top-left|bottom-right|array(<x>, <y>)>
 *  );
 *
 *  // Overwrite original image
 *  Pi::service('image')->watermark(
 *      <path/to/source/image>,
 *      '',
 *      <path/to/watermark/image>,
 *      <top-left|bottom-right|array(<x>, <y>)>
 *  );
 *  Pi::service('image')->watermark(
 *      <path/to/source/image>
 *  );
 * ```
 *
 * - Crop
 * ```
 *  // Crop with specified size
 *  Pi::service('image')->crop(
 *      <path/to/source/image>,
 *      array(<X>, <Y>),
 *      array(<width>, <height>),
 *      <path/to/saved/image>
 *  );
 *
 *  // Crop with ratio size
 *  Pi::service('image')->crop(
 *      <path/to/source/image>,
 *      array(<X>, <Y>),
 *      0.5,
 *      <path/to/saved/image>
 *  );
 *
 *  // Overwrite original image
 *  Pi::service('image')->crop(
 *      <path/to/source/image>,
 *      array(<X>, <Y>),
 *      array(<width>, <height>)
 *  );
 * ```
 *
 * - Resize
 * ```
 *  // Resize with specified size
 *  Pi::service('image')->resize(
 *      <path/to/source/image>,
 *      array(<width>, <height>),
 *      <path/to/saved/image>,
 *      <filter>
 *  );
 *
 *  // Resize with ratio size
 *  Pi::service('image')->resize(
 *      <path/to/source/image>,
 *      0.5,
 *      <path/to/saved/image>,
 *      <filter>
 *  );
 *
 *  // Overwrite original image
 *  Pi::service('image')->resize(
 *      <path/to/source/image>,
 *      array(<width>, <height>)
 *  );
 * ```
 *
 * - Rotate
 * ```
 *  // Rotate
 *  Pi::service('image')->rotate(
 *      <path/to/source/image>,
 *      <angle>,
 *      <path/to/saved/image>,
 *      <background-color>
 *  );
 *
 *  // Overwrite original image
 *  Pi::service('image')->rotate(
 *      <path/to/source/image>,
 *      <angle>,
 *      <background-color>
 *  );
 * ```
 *
 * - Paste
 * ```
 *  // Paste
 *  Pi::service('image')->paste(
 *      <path/to/source/image>,
 *      <path/to/child/image>,
 *      array(<X>, <Y>),
 *      <path/to/saved/image>
 *  );
 *
 *  // Overwrite original image
 *  Pi::service('image')->paste(
 *      <path/to/source/image>,
 *      <path/to/child/image>,
 *      array(<X>, <Y>)
 *  );
 * ```
 *
 * - Thumbnail
 * ```
 *  // Thumbnail with specified size
 *  Pi::service('image')->thumbnail(
 *      <path/to/source/image>,
 *      array(<width>, <height>),
 *      <path/to/saved/image>,
 *      <mode>
 *  );
 *
 *  // Thumbnail with ratio size
 *  Pi::service('image')->thumbnail(
 *      <path/to/source/image>,
 *      0.5,
 *      <path/to/saved/image>,
 *      <mode>
 *  );
 *
 *  // Overwrite original image
 *  Pi::service('image')->thumbnail(
 *      <path/to/source/image>,
 *      array(<width>, <height>)
 *  );
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @see https://github.com/avalanche123/Imagine
 */
class Image extends AbstractService
{
    /** {@inheritDoc} */
    protected $fileIdentifier = 'image';

    /** @var string Image manipulation driver */
    protected $driver;

    /**
     * Get image driver
     *
     * @param string $driver
     * @return ImagineInterface|bool
     */
    public function getDriver($driver = '')
    {
        if (null === $this->driver) {
            $driverName = $driver ?: $this->getOption('driver');
            $driverClass = false;
            switch ($driverName) {
                case 'gd':
                    if (function_exists('gd_info')) {
                        $driverClass = 'Imagine\Gd\Imagine';
                    }
                    break;
                case 'gmagick':
                    if (class_exists('Gmagick')) {
                        $driverClass = 'Imagine\Gmagick\Imagine';
                    }
                    break;
                case 'imagick':
                    if (class_exists('Imagick')) {
                        $driverClass = 'Imagine\Gmagick\Imagine';
                    }
                    break;
                case 'auto':
                default:
                    if (function_exists('gd_info')) {
                        $driverClass = 'Imagine\Gd\Imagine';
                    } elseif (class_exists('Gmagick')) {
                        $driverClass = 'Imagine\Gmagick\Imagine';
                    } elseif (class_exists('Imagick')) {
                        $driverClass = 'Imagine\Gmagick\Imagine';
                    }
                    break;
            }
            if ($driverClass) {
                $this->driver = new $driverClass;
            } else {
                $this->driver = false;
            }
        }

        return $this->driver;
    }

    /**
     * Canonize Box element
     *
     * @param array|int|Box $width   Width or width and height, or Box
     * @param int           $height  Height
     *
     * @return Box
     */
    public function box($width, $height = 0)
    {
        if ($width instanceof Box) {
            $result = $width;
        } elseif (is_array($width)) {
            $result = new Box($width[0], $width[1]);
        } else {
            $result = new Box($width, $height);
        }

        return $result;
    }

    /**
     * Canonize Point element
     *
     * @param array|int|Point $x X or X and Y, or Point
     * @param int $y
     *
     * @return Point
     */
    public function point($x, $y = 0)
    {
        if ($x instanceof Point) {
            $result = $x;
        } elseif (is_array($x)) {
            $result = new Point($x[0], $x[1]);
        } else {
            $result = new Point($x, $y);
        }

        return $result;
    }

    /**
     * Canonize Color element
     *
     * @param array|string|Color $color Color value or color and alpha, or Color
     * @param int $alpha
     *
     * @return Color
     */
    public function color($color, $alpha = 0)
    {
        if ($color instanceof Color) {
            $result = $color;
        } elseif (is_array($color)) {
            $result = new Color($color[0], $color[1]);
        } else {
            $result = new Color($color, $alpha);
        }

        return $result;
    }

    /**
     * Creates a new empty image with an optional background color
     *
     * @param array|Box             $size   Width and height
     * @param string|array|Color    $color  Color value and alpha
     *
     * @return ImageInterface|bool
     */
    public function create($size, $color = null)
    {
        if (!$this->getDriver()) {
            return false;
        }

        $size = $this->box($size);
        $color = $color ? $this->color($color) : null;
        try {
            $image = $this->getDriver()->create($size, $color);
        } catch (\Exception $e) {
            $image = false;
        }

        return $image;
    }

    /**
     * Opens an existing image from $path
     *
     * @param string $path
     *
     * @return ImageInterface|bool
     */
    public function open($path)
    {
        if (!$this->getDriver()) {
            return false;
        }

        try {
            $image = $this->getDriver()->open($path);
        } catch (\Exception $e) {
            $image = false;
        }

        return $image;
    }

    /**
     * Loads an image from a binary $string
     *
     * @param string $string
     *
     * @return ImageInterface|bool
     */
    public function load($string)
    {
        if (!$this->getDriver()) {
            return false;
        }

        try {
            $image = $this->getDriver()->load($string);
        } catch (\Exception $e) {
            $image = false;
        }

        return $image;
    }

    /**
     * Loads an image from a resource $resource
     *
     * @param resource $resource
     *
     * @return ImageInterface|bool
     */
    public function read($resource)
    {
        if (!$this->getDriver()) {
            return false;
        }

        try {
            $image = $this->getDriver()->read($resource);
        } catch (\Exception $e) {
            $image = false;
        }

        return $image;
    }

    /**
     * Constructs a font with specified $file, $size and $color
     *
     * The font size is to be specified in points (e.g. 10pt means 10)
     *
     * @param string  $file
     * @param integer $size
     * @param string|array|Color $color  Color value and alpha
     *
     * @return FontInterface|bool
     */
    public function font($file, $size, $color)
    {
        if (!$this->getDriver()) {
            return false;
        }

        $color = $this->color($color);
        try {
            $font = $this->getDriver()->font($file, $size, $color);
        } catch (\Exception $e) {
            $font = false;
        }

        return $font;
    }

    /**
     * Add watermark to an image
     *
     * @param string|Image          $sourceImage
     * @param string                $to
     * @param string                $watermarkImage
     * @param string|array|Point    $position
     *
     * @return bool
     */
    public function watermark(
        $sourceImage,
        $to = '',
        $watermarkImage = '',
        $position = ''
    ) {
        if (!$this->getDriver()) {
            return false;
        }

        if ($sourceImage instanceof ImageInterface) {
            $image = $sourceImage;
        } else {
            $image = $this->getDriver()->open($sourceImage);
        }
        if ($watermarkImage instanceof ImageInterface) {
            $watermark = $watermarkImage;
        } else {
            $watermarkImage = $watermarkImage ?: $this->getOption('watermark');
            $watermark = $this->getDriver()->open($watermarkImage);
        }
        if ($position instanceof Point) {
            $start = $position;
        } elseif (is_array($position)) {
            $start = $this->point($position[0], $position[1]);
        } else {
            $size      = $image->getSize();
            $wSize     = $watermark->getSize();
            switch ($position) {
                case 'top-left':
                    list($x, $y) = array(0, 0);
                    break;
                case 'top-right':
                    $x = $size->getWidth() - $wSize->getWidth();
                    $y = 0;
                    break;
                case 'bottom-left':
                    $x = 0;
                    $y = $size->getHeight() - $wSize->getHeight();
                    break;
                case 'bottom-right':
                default:
                    $x = $size->getWidth() - $wSize->getWidth();
                    $y = $size->getHeight() - $wSize->getHeight();
                    break;
            }
            $start = $this->point($x, $y);
        }
        try {
            $image->paste($watermark, $start);
            $result = $this->saveImage($image, $to, $sourceImage);
        } catch(\Exception $e) {
            $result = false;
        }

        return $result;
    }

    /**
     * Crops a specified box out of the source image (modifies the source image)
     * Returns cropped self
     *
     * @param string|Image      $sourceImage
     * @param array|Point       $start
     * @param array|float|Box   $size
     * @param string            $to
     *
     * @return bool
     */
    public function crop($sourceImage, $start, $size, $to = '')
    {
        if (!$this->getDriver()) {
            return false;
        }
        if ($sourceImage instanceof ImageInterface) {
            $image = $sourceImage;
        } else {
            $image = $this->getDriver()->open($sourceImage);
        }
        $start = $this->point($start);
        if (is_float($size)) {
            $size = $image->getSize()->scale($size);
        } else {
            $size = $this->box($size);
        }
        try {
            $image->crop($start, $size);
            $result = $this->saveImage($image, $to, $sourceImage);
        } catch(\Exception $e) {
            $result = false;
        }

        return $result;
    }

    /**
     * Resizes current image and returns self
     *
     * @param string|Image      $sourceImage
     * @param array|float|Box   $size
     * @param string            $to
     * @param string            $filter
     *
     * @return bool
     */
    public function resize($sourceImage, $size, $to = '', $filter = '')
    {
        if (!$this->getDriver()) {
            return false;
        }
        if ($sourceImage instanceof ImageInterface) {
            $image = $sourceImage;
        } else {
            $image = $this->getDriver()->open($sourceImage);
        }
        $filter = $filter ?: ImageInterface::FILTER_UNDEFINED;
        if (is_float($size)) {
            $size = $image->getSize()->scale($size);
        } else {
            $size = $this->box($size);
        }
        try {
            $image->resize($size, $filter);
            $result = $this->saveImage($image, $to, $sourceImage);
        } catch(\Exception $e) {
            $result = false;
        }

        return $result;
    }

    /**
     * Rotates an image at the given angle.
     * Optional $background can be used to specify the fill color of the empty
     * area of rotated image.
     *
     * @param string|Image       $sourceImage
     * @param int                $angle
     * @param string             $to
     * @param string|array|Color $background
     *
     * @return bool
     */
    public function rotate($sourceImage, $angle, $to = '', $background = null)
    {
        if (!$this->getDriver()) {
            return false;
        }
        if ($sourceImage instanceof ImageInterface) {
            $image = $sourceImage;
        } else {
            $image = $this->getDriver()->open($sourceImage);
        }
        $background = $background ? $this->color($background) : null;
        try {
            $image->rotate($angle, $background);
            $result = $this->saveImage($image, $to, $sourceImage);
        } catch(\Exception $e) {
            $result = false;
        }

        return $result;
    }

    /**
     * Pastes an image into a parent image
     * Throws exceptions if image exceeds parent image borders or if paste
     * operation fails
     *
     * @param string|Image $sourceImage
     * @param string|Image $childImage
     * @param array|Point  $start
     * @param string       $to
     *
     * @return bool
     */
    public function paste($sourceImage, $childImage, $start, $to)
    {
        if (!$this->getDriver()) {
            return false;
        }
        if ($sourceImage instanceof ImageInterface) {
            $image = $sourceImage;
        } else {
            $image = $this->getDriver()->open($sourceImage);
        }
        if ($childImage instanceof ImageInterface) {
            $child = $childImage;
        } else {
            $child = $this->getDriver()->open($childImage);
        }
        $start = $this->point($start);
        try {
            $image->paste($child, $start);
            $result = $this->saveImage($image, $to, $sourceImage);
        } catch(\Exception $e) {
            $result = false;
        }

        return $result;
    }

    /**
     * Saves the image at a specified path, the target file extension is used
     * to determine file format, only jpg, jpeg, gif, png, wbmp and xbm are
     * supported
     *
     * @param string|Image $sourceImage
     * @param string       $to
     * @param array        $options
     *
     * @return bool
     */
    public function save($sourceImage, $to = '', array $options = array())
    {
        if (!$this->getDriver()) {
            return false;
        }
        if ($sourceImage instanceof ImageInterface) {
            $image = $sourceImage;
        } else {
            $image = $this->getDriver()->open($sourceImage);
        }
        try {
            $result = $this->saveImage($image, $to, '', $options);
        } catch(\Exception $e) {
            $result = false;
        }

        return $result;
    }

    /**
     * Generates a thumbnail from a current image
     * Returns it as a new image, doesn't modify the current image
     *
     * @param string|Image      $sourceImage
     * @param array|float|Box   $size
     * @param string            $to
     * @param string            $mode
     *
     * @return bool|ImageInterface
     */
    public function thumbnail($sourceImage, $size, $to, $mode = '')
    {
        if (!$this->getDriver()) {
            return false;
        }
        if ($sourceImage instanceof ImageInterface) {
            $image = $sourceImage;
        } else {
            $image = $this->getDriver()->open($sourceImage);
        }
        if (is_float($size)) {
            $size = $image->getSize()->scale($size);
        } else {
            $size = $this->box($size);
        }
        $mode = $mode ?: ImageInterface::THUMBNAIL_INSET;
        try {
            $thumbnail = $image->thumbnail($size, $mode);
            $result = $this->saveImage($thumbnail, $to, $sourceImage);
        } catch (\Exception $e) {
            $result = false;
        }

        return $result;
    }

    /**
     * Create path for image file to be stored
     *
     * @param      $file
     * @param bool $isFile
     *
     * @return mixed
     */
    public function mkdir($file, $isFile = true)
    {
        $path = $isFile ? dirname($file) : $file;
        $result = Pi::service('file')->mkdir($path);

        return $result;
    }

    /**
     * Save Image to a file
     *
     * @param ImageInterface        $image
     * @param string                $to
     * @param string|ImageInterface $source
     * @param array                 $options
     *
     * @return bool|ImageInterface
     */
    protected function saveImage(
        ImageInterface $image,
        $to,
        $source = '',
        array $options = array()
    ) {
        if (!$to && $source && !$source instanceof ImageInterface) {
            $to = $source;
        }
        if ($to) {
            $result = true;
            if ($this->getOption('auto_mkdir') && !$this->mkdir($to)) {
                $result = false;
            } else {
                try {
                    $image->save($to, $options);
                } catch (\Excetpion $e) {
                    $result = false;
                }
            }
        } else {
            $result = $image;
        }

        return $result;
    }
}
