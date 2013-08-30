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
 * Uploaded avatar handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Upload extends AbstractAvatar
{
    /**
     * {@inheritDoc}
     */
    public function getSource($uid, $size = '')
    {
        $src = '';
        if (!$uid) {
            return $src;
        }

        $avatar = Pi::user()->get($uid, 'avatar');
        if ($uid == $this->user->get('id')) {
            $avatar = $this->user->get('avatar');
        } else {
            $data = Pi::user()->get($uid, array('avatar', 'email'));
        }
        if ($avatar && false === strpos($avatar, '@')) {
            $src = $this->build($avatar, $size, $uid);
        }

        return $src;
    }

    /**
     * {@inheritDoc}
     */
    public function getSourceList($uids, $size = '')
    {
        $result = array();
        $avatars = Pi::user()->get($uids, 'avatar');
        foreach ($avatars as $uid => $avatar) {
            if ($avatar && false === strpos($avatar, '@')) {
                $result[$uid] = $this->build($avatar, $size, $uid);
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function build($source, $size = '')
    {
        $uid = func_get_args(2);
        $size = $this->canonizeSize($size, false);
        if (!empty($this->options['path'])) {
            $pattern = $this->options['path'];
        } else {
            $pattern = 'upload/avatar/%size%/%uid%_%source%';
        }
        if (is_callable($pattern)) {
            $path = call_user_func_array($pattern, array($source, $size, $uid));
        } else {
            $path = str_replace(
                array('source', 'size', 'uid'),
                array($source, $size, $uid),
                $pattern
            );
        }
        $src = Pi::url($path);

        return $src;
    }
}
