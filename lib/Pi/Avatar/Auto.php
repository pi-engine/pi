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

/**
 * Auto avatar handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Auto extends AbstractAvatar
{
    /** @var array Adapters per user */
    protected $adapters = array();

    /**
     * {@inheritDoc}
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
        $adapter = $this->adapters[$uid];
        if ($size
            && !isset($attributes['width'])
            && !isset($attributes['height'])
            && !$this->hasSizeByAdapter($size, $adapter)
        ) {
            $attributes['width'] = $this->getSizeByAdapter($size, $adapter);
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
        foreach ($srcList as $uid => $src) {
            $attrs = $attributes;
            $adapter = $this->adapters[$uid];
            if ($size
                && !isset($attrs['width'])
                && !isset($attrs['height'])
                && !$this->hasSizeByAdapter($size, $adapter)
            ) {
                $attrs['width'] = $this->getSizeByAdapter($size, $adapter);
            }
            $attrString = '';
            foreach ($attrs as $key => $val) {
                $attrString .= ' ' . $key . '="' . _escape($val) . '"';
            }

            $result[$uid] = sprintf('<img src="%s"%s />', $src, $attrString);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getSource($uid, $size = '')
    {
        $src = '';
        if ($uid) {
            if ($uid == $this->user->get('id')) {
                $avatar = $this->user->get('avatar');
            } else {
                $avatar = Pi::user()->get($uid, 'avatar');
            }

            if ($avatar) {
                $src = $this->buildUrl($avatar, $size, $uid);
            }
        }

        return $src;
    }

    /**
     * {@inheritDoc}
     */
    public function getSourceList($uids, $size = '')
    {
        $result = array();
        $list = Pi::user()->get($uids, 'avatar');
        foreach ($list as $uid => $data) {
            if ($data) {
                $url = $this->buildUrl($data, $size, $uid);
                if ($url) {
                    $result[$uid] = $url;
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function build($source, $size = '', $uid = null)
    {
        return false;
    }

    /**
     * Build avatar URL
     *
     * @param string  $data
     * @param string $size
     * @param int $uid
     *
     * @return string
     */
    protected function buildUrl($data, $size, $uid)
    {
        $src = '';
        if (!empty($this->options['adapter_allowed'])) {
            $allowedAdapters = (array) $this->options['adapter_allowed'];
        } else {
            $allowedAdapters = (array) Pi::avatar()->getOption('adapter');
        }
        $list = array_fill_keys($allowedAdapters, '');

        if ($data) {
            $type = Pi::service('avatar')->getType($data);
            if (isset($list[$type])) {
                $list[$type] = $data;
            }
        }
        foreach ($list as $adapter => $avatar) {
            if ($avatar) {
                $src = Pi::service('avatar')->getAdapter($adapter)
                    ->setForce(false)
                    ->build($avatar, $size, $uid);

                $this->adapters[$uid] = $adapter;
                break;
            }
        }

        return $src;
    }

    /**
     * {@inheritDoc}
     */
    public function getSizeByAdapter($size, $adapter)
    {
        $adapter = Pi::service('avatar')->getAdapter($adapter);
        $result = $adapter ? $adapter->getSize($size) : null;

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function hasSizeByAdapter($size, $adapter)
    {
        $adapter = Pi::service('avatar')->getAdapter($adapter);
        $result = $adapter ? $adapter->hasSize($size) : false;

        return $result;
    }
}
