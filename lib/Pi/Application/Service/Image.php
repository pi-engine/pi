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
 *      <path/to/watermark/image>,
 *      <path/to/saved/image>
 *  );
 *
 *  // Use system watermark
 *  Pi::service('image')->watermark(
 *      <path/to/source/image>,
 *      '',
 *      <path/to/saved/image>
 *  );
 *
 *  // Overwrite original image
 *  Pi::service('image')->watermark(
 *      <path/to/source/image>,
 *      <path/to/watermark/image>
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
 *      <filter>,
 *      <path/to/saved/image>
 *  );
 *
 *  // Resize with ratio size
 *  Pi::service('image')->resize(
 *      <path/to/source/image>,
 *      0.5,
 *      <filter>,
 *      <path/to/saved/image>
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
 *      <background-color>,
 *      <path/to/saved/image>
 *  );
 *
 *  // Overwrite original image
 *  Pi::service('image')->rotate(
 *      <path/to/source/image>,
 *      <angle>,
 *      <background-color>,
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
 *      <path/to/saved/image>,
 *      array(<width>, <height>),
 *      <mode>
 *  );
 *
 *  // Thumbnail with ratio size
 *  Pi::service('image')->thumbnail(
 *      <path/to/source/image>,
 *      <path/to/saved/image>,
 *      0.5,
 *      <mode>
 *  );
 *
 *  // Overwrite original image
 *  Pi::service('image')->thumbnail(
 *      <path/to/source/image>,
 *      '',
 *      array(<width>, <height>),
 *      <mode>
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
            $driverName = $driver ?: $this->options['driver'];
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
     * @param string|Image $sourceImage
     * @param string $watermarkImage
     * @param string $to
     *
     * @return bool
     */
    public function watermark($sourceImage, $watermarkImage = '', $to = '')
    {
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
            $watermarkImage = $watermarkImage ?: $this->getOptions('watermark');
            $watermark = $this->getDriver()->open($watermarkImage);
        }
        $size      = $image->getSize();
        $wSize     = $watermark->getSize();
        $bottomRight = $this->point(
            $size->getWidth() - $wSize->getWidth(),
            $size->getHeight() - $wSize->getHeight()
        );
        try {
            $image->paste($watermark, $bottomRight);
            if ($to) {
                $image->save($to);
            } elseif (!$sourceImage instanceof ImageInterface) {
                $image->save($sourceImage);
            }
            $result = true;
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
            if ($to) {
                $image->save($to);
            } elseif (!$sourceImage instanceof ImageInterface) {
                $image->save($sourceImage);
            }
            $result = true;
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
     * @param string            $filter
     * @param string            $to
     *
     * @return bool
     */
    public function resize($sourceImage, $size, $filter = '', $to = '')
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
            if ($to) {
                $image->save($to);
            } elseif (!$sourceImage instanceof ImageInterface) {
                $image->save($sourceImage);
            }
            $result = true;
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
     * @param string|array|Color $background
     * @param string             $to
     *
     * @return bool
     */
    public function rotate($sourceImage, $angle, $background = null, $to = '')
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
            if ($to) {
                $image->save($to);
            } elseif (!$sourceImage instanceof ImageInterface) {
                $image->save($sourceImage);
            }
            $result = true;
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
            if ($to) {
                $image->save($to);
            } elseif (!$sourceImage instanceof ImageInterface) {
                $image->save($sourceImage);
            }
            $result = true;
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
            if ($to) {
                $image->save($to, $options);
            } elseif (!$sourceImage instanceof ImageInterface) {
                $image->save($sourceImage, $options);
            }
            $result = true;
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
     * @param string            $to
     * @param array|float|Box   $size
     * @param string            $mode
     *
     * @return bool|ImageInterface
     */
    public function thumbnail($sourceImage, $to, $size, $mode = '')
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
            $result = true;
            $thumbnail = $image->thumbnail($size, $mode);
            if ($to) {
                $thumbnail->save($to);
            } else {
                $result = $thumbnail;
            }
        } catch (\Exception $e) {
            $result = false;
        }

        return $result;
    }
}
