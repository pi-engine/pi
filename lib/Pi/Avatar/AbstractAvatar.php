<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Avatar;

use Pi;
//use Pi\User\Resource\Avatar as AvatarResource;
use Pi\User\Model\AbstractModel;

/**
 * User avatar abstract class
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractAvatar
{
    /** @var bool Force to skip type check */
    protected $force = true;

    /**
     * Options
     * @var array
     */
    protected $options;

    /** @var  AbstractModel User model */
    protected $user;

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct(array $options = array())
    {
        if ($options) {
            $this->setOptions($options);
        }
    }

    /**
     * Set to force this type
     *
     * @param bool $flag
     *
     * @return $this
     */
    public function setForce($flag)
    {
        $this->force = (bool) $flag;

        return $this;
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
     * Get options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set user model
     *
     * @param AbstractModel $user
     *
     * @return $this
     */
    public function setUser(AbstractModel $user)
    {
        $this->user = $user;

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

        if (false === $attributes) {
            return $src;
        }

        if (is_string($attributes)) {
            $attributes = array(
                'alt'   => $attributes,
            );
        } elseif (!isset($attributes['alt'])) {
            $attributes['alt'] = '';
        }
        if ($size
            && !isset($attributes['width'])
            && !isset($attributes['height'])
            && !$this->hasSize($size)
        ) {
            $attributes['width'] = $this->getSize($size);
        }
        $attrString = '';
        foreach ($attributes as $key => $val) {
            $attrString .= ' ' . $key . '="' . _escape($val) . '"';
        }
        $result = sprintf('<img src="%s"%s />', $src, $attrString);

        return $result;
    }

    /**
     * Get avatars of a list of users
     *
     * @param int[]  $uids
     * @param string $size
     * @param array  $attributes
     *
     * @return array
     */
    public function getList($uids, $size = '', $attributes = array())
    {
        $result = array();
        $srcList = $this->getSourceList($uids, $size);
        if (false === $attributes) {
            return $srcList;
        }

        if (is_string($attributes)) {
            $attributes = array(
                'alt'   => $attributes,
            );
        } elseif (!isset($attributes['alt'])) {
            $attributes['alt'] = '';
        }
        if ($size
            && !isset($attributes['width'])
            && !isset($attributes['height'])
            && !$this->hasSize($size)
        ) {
            $attributes['width'] = $this->getSize($size);
        }
        $attrString = '';
        foreach ($attributes as $key => $val) {
            $attrString .= ' ' . $key . '="' . _escape($val) . '"';
        }
        foreach ($srcList as $uid => $src) {
            $result[$uid] = sprintf('<img src="%s"%s />', $src, $attrString);
        }

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
     * Get user avatar links
     *
     * @param int[]  $uids
     * @param string $size
     *
     * @return array
     */
    abstract public function getSourceList($uids, $size = '');

    /**
     * Build user avatar link from corresponding source
     *
     * @param string    $source
     * @param string    $size
     *      Size of image to display, integer for width, string for named size:
     *      'mini', 'xsmall', 'small', 'medium', 'large', 'xlarge', 'xxlarge'
     *
     * @param int|null  $uid
     *
     * @return string
     */
    abstract public function build($source, $size = '', $uid = null);

    /**
     * Canonize sie
     *
     * Convert named size to numeric size or convert from number to named size
     *
     * @param string|int $size
     *
     * @param bool       $toInt
     *
     * @return int|string
     */
    public function canonizeSize($size, $toInt = true)
    {
        $sizeMap = array();
        if (!empty($this->options['size_list'])) {
            $sizeList = Pi::service('avatar')->getSize();
            foreach ($this->options['size_list'] as $name) {
                if (isset($sizeList[$name])) {
                    $sizeMap[$name] = $sizeList[$name];
                }
            }
        }

        return Pi::service('avatar')->canonizeSize($size, $toInt, $sizeMap);
    }

    /**
     * Get size number of a specific size or a list of defined sizes
     *
     * @param string $size
     *
     * @return array|int|bool
     */
    public function getSize($size = '')
    {
        if ($size) {
            $result = Pi::service('avatar')->getSize($size);
        } else {
            $result = array();
            $sizeList = Pi::service('avatar')->getSize();
            if (!empty($this->options['size_list'])) {
                foreach ($this->options['size_list'] as $name) {
                    if (isset($sizeList[$name])) {
                        $result[$name] = $sizeList[$name];
                    }
                }
            } else {
                $result = $sizeList;
            }
        }

        return $result;
    }

    /**
     * Check if a named size is available
     *
     * @param string $size
     *
     * @return bool
     */
    public function hasSize($size)
    {
        if (empty($this->options['size_list'])
            || in_array($size, $this->options['size_list'])
        ) {
            return true;
        }

        return false;
    }
}
