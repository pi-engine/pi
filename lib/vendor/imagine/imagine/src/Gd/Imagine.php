<?php

/*
 * This file is part of the Imagine package.
 *
 * (c) Bulat Shakirzyanov <mallluhuct@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Imagine\Gd;

use Imagine\Exception\InvalidArgumentException;
use Imagine\Exception\RuntimeException;
use Imagine\Factory\ClassFactoryInterface;
use Imagine\File\LoaderInterface;
use Imagine\Image\AbstractImagine;
use Imagine\Image\BoxInterface;
use Imagine\Image\Metadata\MetadataBag;
use Imagine\Image\Palette\Color\ColorInterface;
use Imagine\Image\Palette\Color\RGB as RGBColor;
use Imagine\Image\Palette\PaletteInterface;
use Imagine\Image\Palette\RGB;
use Imagine\Utils\ErrorHandling;

/**
 * Imagine implementation using the GD library.
 */
final class Imagine extends AbstractImagine
{
    /**
     * Initialize the class.
     */
    public function __construct()
    {
        $this->requireGdVersion('2.0.1');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Imagine\Image\ImagineInterface::create()
     */
    public function create(BoxInterface $size, ColorInterface $color = null)
    {
        $width = $size->getWidth();
        $height = $size->getHeight();

        $resource = imagecreatetruecolor($width, $height);

        if (false === $resource) {
            throw new RuntimeException('Create operation failed');
        }

        $palette = null !== $color ? $color->getPalette() : new RGB();
        $color = $color ? $color : $palette->color('fff');

        if (!$color instanceof RGBColor) {
            throw new InvalidArgumentException('GD driver only supports RGB colors');
        }

        $index = imagecolorallocatealpha($resource, $color->getRed(), $color->getGreen(), $color->getBlue(), round(127 * (100 - $color->getAlpha()) / 100));

        if (false === $index) {
            throw new RuntimeException('Unable to allocate color');
        }

        if (false === imagefill($resource, 0, 0, $index)) {
            throw new RuntimeException('Could not set background color fill');
        }

        if ($color->getAlpha() <= 5) {
            imagecolortransparent($resource, $index);
        }

        return $this->wrap($resource, $palette, new MetadataBag());
    }

    /**
     * {@inheritdoc}
     *
     * @see \Imagine\Image\ImagineInterface::open()
     */
    public function open($path)
    {
        $loader = $path instanceof LoaderInterface ? $path : $this->getClassFactory()->createFileLoader($path);
        $path = $loader->getPath();

        $data = $loader->getData();

        $resource = $this->createImageFromString($data);

        if (!\is_resource($resource)) {
            throw new RuntimeException(sprintf('Unable to open image %s', $path));
        }

        return $this->wrap($resource, new RGB(), $this->getMetadataReader()->readFile($loader));
    }

    /**
     * {@inheritdoc}
     *
     * @see \Imagine\Image\ImagineInterface::load()
     */
    public function load($string)
    {
        return $this->doLoad($string, $this->getMetadataReader()->readData($string));
    }

    /**
     * {@inheritdoc}
     *
     * @see \Imagine\Image\ImagineInterface::read()
     */
    public function read($resource)
    {
        if (!\is_resource($resource)) {
            throw new InvalidArgumentException('Variable does not contain a stream resource');
        }

        $content = stream_get_contents($resource);

        if (false === $content) {
            throw new InvalidArgumentException('Cannot read resource content');
        }

        return $this->doLoad($content, $this->getMetadataReader()->readData($content, $resource));
    }

    /**
     * {@inheritdoc}
     *
     * @see \Imagine\Image\ImagineInterface::font()
     */
    public function font($file, $size, ColorInterface $color)
    {
        return $this->getClassFactory()->createFont(ClassFactoryInterface::HANDLE_GD, $file, $size, $color);
    }

    /**
     * @param resource $resource
     * @param \Imagine\Image\Palette\PaletteInterface $palette
     * @param \Imagine\Image\Metadata\MetadataBag $metadata
     *
     * @throws \Imagine\Exception\RuntimeException
     *
     * @return \Imagine\Image\ImageInterface
     */
    private function wrap($resource, PaletteInterface $palette, MetadataBag $metadata)
    {
        if (!imageistruecolor($resource)) {
            if (\function_exists('imagepalettetotruecolor')) {
                if (false === imagepalettetotruecolor($resource)) {
                    throw new RuntimeException('Could not convert a palette based image to true color');
                }
            } else {
                list($width, $height) = array(imagesx($resource), imagesy($resource));

                // create transparent truecolor canvas
                $truecolor = imagecreatetruecolor($width, $height);
                $transparent = imagecolorallocatealpha($truecolor, 255, 255, 255, 127);

                imagealphablending($truecolor, false);
                imagefilledrectangle($truecolor, 0, 0, $width, $height, $transparent);
                imagealphablending($truecolor, false);

                imagecopy($truecolor, $resource, 0, 0, 0, 0, $width, $height);

                imagedestroy($resource);
                $resource = $truecolor;
            }
        }

        if (false === imagealphablending($resource, false) || false === imagesavealpha($resource, true)) {
            throw new RuntimeException('Could not set alphablending, savealpha and antialias values');
        }

        if (\function_exists('imageantialias')) {
            imageantialias($resource, true);
        }

        return $this->getClassFactory()->createImage(ClassFactoryInterface::HANDLE_GD, $resource, $palette, $metadata);
    }

    /**
     * @param string $version
     *
     * @throws \Imagine\Exception\RuntimeException
     */
    private function requireGdVersion($version)
    {
        if (!\function_exists('gd_info')) {
            throw new RuntimeException('Gd not installed');
        }
        if (version_compare(GD_VERSION, $version, '<')) {
            throw new RuntimeException(sprintf('GD2 version %s or higher is required, %s provided', $version, GD_VERSION));
        }
    }

    /**
     * @param string $string
     * @param \Imagine\Image\Metadata\MetadataBag $metadata
     *
     * @throws \Imagine\Exception\RuntimeException
     *
     * @return \Imagine\Image\ImageInterface
     */
    private function doLoad($string, MetadataBag $metadata)
    {
        $resource = $this->createImageFromString($string);

        if (!\is_resource($resource)) {
            throw new RuntimeException('An image could not be created from the given input');
        }

        return $this->wrap($resource, new RGB(), $metadata);
    }

    /**
     * Check if the raw image data represents an image in WebP format.
     *
     * @param string $data
     *
     * @return bool
     */
    private function isWebP(&$data)
    {
        return substr($data, 8, 7) === 'WEBPVP8';
    }

    /**
     * Create an image resource starting from its raw daa.
     *
     * @param string $string
     *
     * @return resource|false
     */
    private function createImageFromString(&$string)
    {
        return ErrorHandling::ignoring(-1, function () use (&$string) {
            //  imagecreatefromstring() does not support webp images before PHP 7.3.0
            if (PHP_VERSION_ID < 70300 && function_exists('imagecreatefromwebp') && $this->isWebP($string)) {
                return @imagecreatefromwebp('data:image/webp;base64,' . base64_encode($string));
            }

            return @imagecreatefromstring($string);
        });
    }
}
