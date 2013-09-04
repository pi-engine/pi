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
            $upload     = '';
            $select     = '';
            $gravatar   = '';
            if ($uid == $this->user->get('id')) {
                $data = array(
                    'avatar'    => $this->user->get('avatar'),
                    'email'     => $this->user->get('email'),
                );
            } else {
                $data = Pi::user()->get($uid, array('avatar', 'email'));
            }
            if ($data) {
                if (!$data['avatar']) {
                    $gravatar = $data['email'];
                } elseif (preg_match('/[a-z0-9\-]/i', $data['avatar'])) {
                    $select = $data['avatar'];
                } elseif (false === strpos($data['avatar'], '@')) {
                    $upload = $data['avatar'];
                } else {
                    $gravatar = $data['avatar'];
                }
            }

            if ($upload) {
                $src = Pi::service('avatar')->getAdapter('upload')->build($upload, $size);
            } elseif ($select) {
                $src = Pi::service('avatar')->getAdapter('select')->build($select, $size);
            } elseif ($gravatar) {
                $src = Pi::service('avatar')->getAdapter('gravatar')->build($gravatar, $size);
            }
        }

        /*
        if (!$src) {
            $src = $this->resource->getAdapter('local')->getSource($uid, $size);
        }
        */

        return $src;
    }

    /**
     * {@inheritDoc}
     */
    public function getSourceList($uids, $size = '')
    {
        $result = array();
        $list = Pi::user()->get($uids, array('avatar', 'email'));
        foreach ($list as $uid => $data) {
            if ($data) {
                $upload     = '';
                $select     = '';
                $gravatar   = '';
                if (!$data['avatar']) {
                    $gravatar = $data['email'];
                } elseif (preg_match('/[a-z0-9\-]/i', $data['avatar'])) {
                    $select = $data['avatar'];
                } elseif (false === strpos($data['avatar'], '@')) {
                    $upload = $data['avatar'];
                } else {
                    $gravatar = $data['avatar'];
                }
                if ($upload) {
                    $src = Pi::service('avatar')->getAdapter('upload')->build($upload, $size);
                } elseif ($select) {
                    $src = Pi::service('avatar')->getAdapter('select')->build($select, $size);
                } elseif ($gravatar) {
                    $src = Pi::service('avatar')->getAdapter('gravatar')->build($gravatar, $size);
                }
                if ($src) {
                    $result[$uid] = $src;
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
}
