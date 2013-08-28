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
use Pi\User\Avatar\AbstractAvatar;

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
    /** @var  AbstractAvatar[] Avatar adapter container */
    protected $adapter;

    /**
     * Get user avatar img element
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
        $avatar = $this->getAdapter()->get($uid, $size, $attributes);
        if (!$avatar) {
            $avatar = $this->getAdapter('local')->get($uid, $size, $attributes);
        }

        return $avatar;
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
        $avatars = $this->getAdapter()->getList($uids, $size, $attributes);
        $missingUids = array();
        foreach ($uids as $uid) {
            if (empty($avatars[$uid])) {
                $missingUids[] = $uid;
            }
        }
        if ($missingUids) {
            $list = $this->getAdapter('local')->getList(
                $uids,
                $size,
                $attributes
            );
            $avatars = array_merge($list, $avatars);
        }

        return $avatars;

    }

    /**
     * Get avatar adapter
     *
     * @param string $adapter
     * @return AbstractAvatar
     */
    public function getAdapter($adapter = '')
    {
        $adapterName = $adapter ?: $this->options['adapter'];

        if (empty($this->adapter[$adapterName])) {
            if (false === strpos($adapterName, '\\')) {
                $class = 'Pi\User\Avatar\\' . ucfirst($adapterName);
            } else {
                $class = $adapterName;
            }
            $adapter = new $class($this);
            if (isset($this->options['options'])) {
                $adapter->setOptions((array) $this->options['options']);
            }
            $this->adapter[$adapterName] = $adapter;
        }

        return $this->adapter[$adapterName];
    }
}
