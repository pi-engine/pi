<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\User\Resource;

use Pi;

/**
 * Avatar handler
 *
 * Avatar APIs;
 *
 *   - avatar->get($uid, [$size[, $attributes[, $source]]])
 *   - avatar->getList($ids[, $size[, $attributes[, $source]]])
 *   - avatar->setSource($uid, $source)
 *   - avatar->set($uid, $value[, $source])
 *   - avatar->delete($uid)
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Avatar extends AbstractResource
{
    /**
     * Get avatar adapter
     *
     * @param string $adapter
     * @return AbstractAvatar
     */
    public function getAdapter($adapter)
    {
        $class = 'Pi\User\Avatar\\' . ucfirst($adapter);
        $adapter = new $class($this->model);

        return $adapter;
    }

    /**
     * Build avatar img
     *
     * @param string $src
     * @param array|string|bool $attributes
     * @return string
     */
    public function build($src, $attributes = array())
    {
        if (false === $attributes) {
            $result = $src;
        } else {
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
            //$img = '<img src="%s"%s />';
            $result = sprintf('<img src="%s"%s />', $src, $attrs);
        }

        return $result;
    }

    /**
     * Get user avatar img element
     *
     * @param int               $uid
     * @param string            $size
     *      Size of image to display, integer for width, string for named size:
     *      'mini', 'xsmall', 'small', 'medium', 'large', 'xlarge', 'xxlarge'
     * @param array|string|bool $attributes
     *      Array for attributes of HTML img element of img,
     *      string for alt of img, false to return URL
     *
     * @return string
     */
    public function get($uid, $size = '', $attributes = array())
    {
        $avatar = $this->model ? $this->model->avatar : '';
        if (false !== strpos($avatar, '@')) {
            $adapter = 'gravatar';
        } elseif ($avatar) {
            $adapter = 'upload';
        } else {
            $adapter = 'local';
        }
        $src = $this->getAdapter($adapter)->build($size);
        $avatar = $this->build($src, $attributes);

        return $avatar;
    }

    /**
     * Get user avatar img element through Gravatar
     *
     * @param int               $size           Size of image to display
     * @param array|string|bool $attributes
     *      Array for attributes of HTML img element of img,
     *      string for alt of img, false to return URL
     * @return string
     */
    public function getGravatar($size = 80, $attributes = array())
    {
        $src = $this->getAdapter('gravatar')->build($size, $attributes);
        $avatar = $this->build($src, $attributes);

        return $avatar;
    }

    /**
     * Get path to uploaded avatar(s)
     *
     * @param string $size
     * @return array|string
     */
    public function getPath($size = null)
    {
        $path = $this->getAdapter('upload')->getPath($size);

        return $path;
    }
}
