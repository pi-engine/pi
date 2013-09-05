<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
    /** @var AvatarResource Avatar resource handler */
    //protected $resource;

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
        $attrs = '';
        foreach ($attributes as $key => $val) {
            $attrs .= ' ' . $key . '="' . _escape($val) . '"';
        }
        $result = sprintf('<img src="%s"%s />', $src, $attrs);

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
        $attrs = '';
        foreach ($attributes as $key => $val) {
            $attrs .= ' ' . $key . '="' . _escape($val) . '"';
        }
        foreach ($srcList as $uid => $src) {
            $result[$uid] = sprintf('<img src="%s"%s />', $src, $attrs);
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
        return Pi::service('avatar')->canonizeSize($size, $toInt);
    }
}
