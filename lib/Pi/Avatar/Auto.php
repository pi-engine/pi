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

/**
 * Auto avatar handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Auto extends AbstractAvatar
{
    /**
     * {@inheritDoc}
     */
    public function getSource($uid, $size = '')
    {
        $src = '';
        if ($uid) {
            if ($uid == $this->user->get('id')) {
                /*
                $data = array(
                    'avatar'    => $this->user->get('avatar'),
                    'email'     => $this->user->get('email'),
                );
                */
                $avatar = $this->user->get('avatar');
            } else {
                //$data = Pi::user()->get($uid, array('avatar', 'email'));
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
        //$list = Pi::user()->get($uids, array('avatar', 'email'));
        $list = Pi::user()->get($uids, 'avatar');
        foreach ($list as $uid => $data) {
            if ($data) {
                $url = $this->buildUrl($data, $size);
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

        /*
        if (!$data['avatar']) {
            if (isset($list['gravatar']) && isset($data['email'])) {
                $list['gravatar'] = $data['email'];
            }
        } else {
            $type = Pi::service('avatar')->getType($data['avatar']);
            if (isset($list[$type])) {
                $list[$type] = $data['avatar'];
            }
        }
        */

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
                break;
            }
        }

        return $src;
    }
}
