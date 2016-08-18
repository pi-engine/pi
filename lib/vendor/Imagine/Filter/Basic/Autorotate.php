<?php

/*
 * This file is part of the Imagine package.
 *
 * (c) Bulat Shakirzyanov <mallluhuct@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Imagine\Filter\Basic;

use Imagine\Filter\FilterInterface;
use Imagine\Image\ImageInterface;
use Imagine\Image\Palette\Color\ColorInterface;

/**
 * Rotates an image automatically based on exif information.
 *
 * Your attention please: This filter requires the use of the
 * ExifMetadataReader to work.
 *
 * @see https://imagine.readthedocs.org/en/latest/usage/metadata.html
 */
class Autorotate implements FilterInterface
{
    private $color;

    /**
     * @param string|array|ColorInterface $color A color
     */
    public function __construct($color = '000000')
    {
        $this->color = $color;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(ImageInterface $image)
    {
        $metadata = $image->metadata();

        switch (isset($metadata['ifd0.Orientation']) ? $metadata['ifd0.Orientation'] : null) {
            case 1: // top-left
                break;
            case 2: // top-right
                $image->flipHorizontally();
                break;
            case 3: // bottom-right
                $image->rotate(180, $this->getColor($image));
                break;
            case 4: // bottom-left
                $image->flipHorizontally();
                $image->rotate(180, $this->getColor($image));
                break;
            case 5: // left-top
                $image->flipHorizontally();
                $image->rotate(-90, $this->getColor($image));
                break;
            case 6: // right-top
                $image->rotate(90, $this->getColor($image));
                break;
            case 7: // right-bottom
                $image->flipHorizontally();
                $image->rotate(90, $this->getColor($image));
                break;
            case 8: // left-bottom
                $image->rotate(-90, $this->getColor($image));
                break;
            default: // Invalid orientation
                break;
        }

        return $image;
    }

    private function getColor(ImageInterface $image)
    {
        if ($this->color instanceof ColorInterface) {
            return $this->color;
        }

        return $image->palette()->color($this->color);
    }
}
