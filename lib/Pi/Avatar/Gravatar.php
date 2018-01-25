<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Avatar;

use Pi;

/**
 * Gravatar handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Gravatar extends AbstractAvatar
{
    /**
     * {@inheritDoc}
     */
    public function getSource($uid, $size = 80)
    {
        $gravatar = '';
        if ($uid) {
            if ($this->force) {
                if ($uid == $this->user->get('id')) {
                    $data = [
                        'avatar' => $this->user->get('avatar'),
                        'email'  => $this->user->get('email'),
                    ];
                } else {
                    $data = Pi::user()->get($uid, ['avatar', 'email']);
                }
                if ($data) {
                    if ($data['avatar']) {
                        $gravatar = $data['avatar'];
                    } else {
                        $gravatar = $data['email'];
                    }
                }
            } else {
                if ($uid == $this->user->get('id')) {
                    $avatar = $this->user->get('avatar');
                } else {
                    $avatar = Pi::user()->get($uid, 'avatar');
                }
                if ($avatar
                    && 'gravatar' == Pi::service('avatar')->getType($avatar)
                ) {
                    $gravatar = $avatar;
                }
            }
        }

        $src = $this->build($gravatar, $size);

        return $src;
    }

    /**
     * {@inheritDoc}
     */
    public function getSourceList($uids, $size = 80)
    {
        $result = [];
        if ($this->force) {
            $list = Pi::user()->get($uids, ['avatar', 'email']);
            foreach ($list as $uid => $data) {
                if ($data) {
                    if ($data['avatar']) {
                        $gravatar = $data['avatar'];
                    } else {
                        $gravatar = $data['email'];
                    }
                    $result[$uid] = $this->build($gravatar, $size);
                }
            }
        } else {
            $list = Pi::user()->get($uids, 'avatar');
            foreach ($list as $uid => $avatar) {
                if ($avatar && 'gravatar' == Pi::service('avatar')->getType($avatar)) {
                    $result[$uid] = $this->build($avatar, $size);
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
        $size = $this->canonizeSize($size);
        $src  = $this->getUrl($source, $size);

        return $src;
    }

    /**
     * {@inheritDoc}
     */
    public function getUrl($email, $size = 80)
    {
        $src     = '%s://www.gravatar.com/avatar/%s%s?s=%d&amp;d=%s&amp;r=%s';
        $hash    = md5(strtolower($email));
        $options = $this->options;
        $src     = sprintf(
            $src,
            !empty($options['secure']) ? 'https' : 'http',
            $hash,
            isset($options['extension'])
                ? '.' . $options['extension'] : '',
            $size,
            isset($options['default']) ? $options['default'] : 'mm',
            isset($options['rate']) ? $options['rate'] : 'g'
        );

        return $src;
    }
}
