<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Demo\Controller\Front;

use Pi;
use Pi\Mvc\Controller\ActionController;

class ImageController extends ActionController
{
    public function indexAction()
    {
        $path = Pi::path('upload/demo/image');
        $origin = $path . '/origin.png';

        if (is_file($origin)) {
            $redirect = $this->url('', array('action' => 'process'));
            $response = <<<EOT
    <p><a href="{$redirect}" title="Click to process">Click to process.</a></p>
EOT;
        } else {
            $response = <<<EOT
    <p>Image resource is required.</p>
EOT;
        }

        return $response;
    }

    /**
     * Test for image service
     */
    public function processAction()
    {
        $path = Pi::path('upload/demo/image');
        $origin = $path . '/origin.png';

        $ops = array('thumbnail', 'resize', 'crop', 'watermark');

        // Resize
        if (in_array('resize', $ops)):
            $resize = function ($size, $name) use ($origin, $path) {
                $image = Pi::service('image')->resize($origin, $size, $path . '/resize/' . $name);
                return $image;
            };
            $large = $resize(array(1024, 0), 'large.png');
            $medium = $resize(array(800, 0), 'medium.png');
            $small = $resize(array(640, 0), 'small.png');
            $specified = $resize(array(640, 500), 'specified.png');
            $height = $resize(array(0, 500), 'height.png');
            $ratio = $resize(0.2, 'ratio.png');
            $proportion = $resize(array(640, 500, true), 'proportion.png');
            $square = $resize(500, 'square.png');
        endif;

        // Thumbnail
        if (in_array('thumbnail', $ops)):
            $thumbnail = function ($size, $name) use ($origin, $path) {
                $image = Pi::service('image')->thumbnail($origin, $size, $path . '/thumbnail/' . $name);
                return $image;
            };
            $width = $thumbnail(array(64, 0), 'width.png');
            $height = $thumbnail(array(0, 50), 'height.png');
            $ratio = $thumbnail(0.02, 'ratio.png');
            $proportion = $thumbnail(array(64, 50, true), 'proportion.png');
            $specified = $thumbnail(array(64, 50), 'specified.png'); // No effect
            $square = $thumbnail(50, 'square.png'); // No effect
        endif;

        // Switch original image
        $origin = $path . '/medium.png';

        // Crop
        if (in_array('crop', $ops)):
            $start = array(100, 200);
            $crop = function ($start, $size, $name) use ($origin, $path) {
                $image = Pi::service('image')->crop($origin, $start, $size, $path . '/crop/' . $name);
                return $image;
            };

            $width = $crop($start, array(480, 0), 'width.png');
            $height = $crop($start, array(0, 300), 'height.png');
            $ratio = $crop($start, 0.2, 'ratio.png');
            $specified = $crop($start, array(640, 500), 'specified.png');
            $square = $crop($start, 500, 'square.png');
        endif;

        // Watermark
        if (in_array('watermark', $ops)):
            $mark = function ($watermark, $position, $name) use ($origin, $path) {
                $image = Pi::service('image')->watermark($origin, $path . '/watermark/' . $name, $watermark, $position);
                return $image;
            };

            $default = $mark('', '', 'default.png');
            $defaultTopLeft = $mark('', 'top-left', 'default-top-left.png');
            $defaultTopRight = $mark('', 'top-right', 'default-top-right.png');
            $defaultBottomLeft = $mark('', 'bottom-left', 'default-bottom-left.png');
            $defaultBottomRight = $mark('', 'bottom-right', 'default-bottom-right.png');
            $defaultSpecified = $mark('', array(500, 200), 'default-specified.png');

            $watermark = $path . '/watermark.png';
            $specifiedTopLeft = $mark($watermark, 'top-left', 'specified-top-left.png');
            $specifiedTopRight = $mark($watermark, 'top-right', 'specified-top-right.png');
            $specifiedBottomLeft = $mark($watermark, 'bottom-left', 'specified-bottom-left.png');
            $specifiedBottomRight = $mark($watermark, 'bottom-right', 'specified-bottom-right.png');
            $specifiedSpecified = $mark($watermark, array(500, 200), 'specified.png');
        endif;

        return 'Manipulation completed with operation(s): ' . implode(', ', $ops);
    }
}