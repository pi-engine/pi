<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\User\Avatar;

/**
 * User avatar abstract class
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractAvatar
{
    /**
     * Options
     * @var array
     */
    protected $options;

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        $this->setOptions($options);
    }

    /**
     * Set options
     *
     * @param array $options
     *
     * @return $this
     */
    public function setOptions(array $options = array())
    {
        $this->options = $options;

        return $this;
    }


    /**
     * Build avatar img element
     *
     * @param int               $uid
     * @param string            $size
     *      Size of image to display, integer for width, string for named size:
     *      'mini', 'xsmall', 'small', 'medium', 'large', 'xlarge', 'xxlarge'
     * @param array|string|bool $attributes
     *      Array for attributes of HTML img element of img,
     *      string for alt of img, false to return img src
     *
     * @return string
     */
    public function get($uid, $size = '', $attributes = array())
    {
        $result = false;

        $src = $this->getSource($uid, $size);
        if (!$src) {
            return $result;
        }

        if (is_string($attributes)) {
            $attributes = array(
                'alt'   => $attributes,
            );
        } elseif (!isset($attributes['alt'])) {
            $attributes['alt'] = '';
        }
        $attrs = '';
        foreach ($attributes as $key => $val) {
            $attrs .= ' ' . $key . '="' . _escape($val) . '"';
        }
        $result = sprintf('<img src="%s"%s />', $src, $attrs);

        return $result;
    }

    /**
     * Get user avatar link
     *
     * @param int    $uid
     * @param string $size
     *      Size of image to display, integer for width, string for named size:
     *      'mini', 'xsmall', 'small', 'medium', 'large', 'xlarge', 'xxlarge'
     *
     * @return string
     */
    abstract public function getSource($uid, $size = '');


    /**
     * Canonize sie
     *
     * @param string|int $size
     *
     * @param bool       $toInt
     *
     * @return int|string
     */
    protected function canonizeSize($size, $toInt = true)
    {
        $sizeMap = $this->options['size_map'];
        if ($toInt) {
            if (!is_numeric($size)) {
                if (!isset($sizeMap[$size])) {
                    $size = $sizeMap['normal'];
                } else {
                    $size = $sizeMap[$size];
                    if (!is_numeric($size)) {
                        $size = $sizeMap[$size];
                    }
                }
            }
        } else {
            if (is_numeric($size)) {
                foreach ($sizeMap as $name => $number) {
                    if (!is_numeric($number) || $number < $size) {
                        continue;
                    } elseif ($number == $size) {
                        break;
                    }
                }
                $size = $name;
            }
        }

        return $size;
    }
}
