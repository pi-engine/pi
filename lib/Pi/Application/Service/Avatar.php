<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;
use Pi\User\Model\AbstractModel;
use Pi\Avatar\AbstractAvatar;


/**
 * Avatar service
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Avatar extends AbstractService
{
    /** {@inheritDoc} */
    protected $fileIdentifier = 'avatar';

    /** @var  AbstractModel User model */
    protected $user;

    /** @var  AbstractAvatar[] Avatar adapters */
    protected $adapter;

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
     * Fetch user module
     *
     * @return AbstractModel
     */
    protected function getUser()
    {
        if (!$this->user) {
            $this->user = Pi::service('user')->getUser();
        }

        return $this->user;
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
     *      string for alt of img,
     *      false to return img src
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
     * @param array|bool|string  $attributes
     *
     * @return array
     */
    public function getList(array $uids, $size = '', $attributes = array())
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
                $missingUids,
                $size,
                $attributes
            );
            $avatars = $avatars + $list;
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
        $adapter = $adapter ?: $this->getOption('adapter');
        if (is_array($adapter)) {
            $adapterName = 'auto';
            $this->options['auto']['adapter_allowed'] = $adapter;
        } else {
            $adapterName = $adapter;
        }

        if (empty($this->adapter[$adapterName])) {
            if (false === strpos($adapterName, '\\')) {
                $class = 'Pi\Avatar\\' . ucfirst($adapterName);
            } else {
                $class = $adapterName;
            }
            if (class_exists($class)) {
                $adapter = new $class;
                if (isset($this->options[$adapterName])) {
                    $adapter->setOptions((array) $this->options[$adapterName]);
                }
                $adapter->setUser($this->getUser());
            } else {
                $adapter = false;
            }
            $this->adapter[$adapterName] = $adapter;
        }

        return $this->adapter[$adapterName];
    }

    /**
     * Magic method to load avatar adapter
     *
     * @param $var
     *
     * @return AbstractAvatar
     */
    public function __get($var)
    {
        return $this->getAdapter($var);
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
        $sizeMap = $this->getOption('size_map');
        if ($size) {
            $result = isset($sizeMap[$size]) ? $sizeMap[$size] : false;
        } else {
            $result = $sizeMap;
        }

        return $result;
    }

    /**
     * Canonize sie
     *
     * Convert named size to numeric size or convert from number to named size
     *
     * @param string|int $size
     * @param bool       $toInt
     * @param array      $sizeMap
     *
     * @return int|string
     */
    public function canonizeSize($size, $toInt = true, $sizeMap = array())
    {
        $sizeMap = $sizeMap ?: $this->getOption('size_map');

        $findSize = function ($size) use ($sizeMap) {
            if (!isset($sizeMap[$size])) {
                $size = $sizeMap['normal'];
            } else {
                $size = $sizeMap[$size];
            }
            return $size;
        };

        // Get numeric size
        if ($toInt) {
            // From named to numeric
            if (!is_numeric($size)) {
                $size = $findSize($size);
            // Canonize numeric to defined numeric
            } else {
                foreach ($sizeMap as $name => $number) {
                    if ($number >= $size) {
                        break;
                    }
                }
                $size = $number;
            }
        // Get named size
        } else {
            // From numeric to named size
            if (!is_numeric($size)) {
                $size = $findSize($size);
            }

            foreach ($sizeMap as $name => $number) {
                if ($number >= $size) {
                    break;
                }
            }
            $size = $name;
        }

        return $size;

    }

    /**
     * Detect source type
     *
     * @param string $source
     *
     * @return string
     */
    public function getType($source)
    {
        if (false !== strpos($source, '@')) {
            $type = 'gravatar';
        } else {
            $uploadConfig = $this->getOption('upload');
            $allowedExtensions = $uploadConfig['extension'];
            $ext = strtolower(pathinfo($source, PATHINFO_EXTENSION));
            if ($ext && in_array($ext, $allowedExtensions)) {
                $type = 'upload';
            } elseif (preg_match('/[a-z0-9\-\_]/i', $source)) {
                $type = 'select';
            } else {
                $type = '';
            }
        }

        return $type;
    }
}
