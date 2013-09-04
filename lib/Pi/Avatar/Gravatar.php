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
            if ($uid == $this->user->get('id')) {
                $data = array(
                    'avatar'    => $this->user->get('avatar'),
                    'email'     => $this->user->get('email'),
                );
            } else {
                $data = Pi::user()->get($uid, array('avatar', 'email'));
            }
            if ($data) {
                if (false === strpos($data['avatar'], '@')) {
                    $gravatar = $data['email'];
                } else {
                    $gravatar = $data['avatar'];
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
        $result = array();
        $list = Pi::user()->get($uids, array('avatar', 'email'));
        foreach ($list as $uid => $data) {
            if ($data) {
                if (false === strpos($data['avatar'], '@')) {
                    $gravatar = $data['email'];
                } else {
                    $gravatar = $data['avatar'];
                }
                $result[$uid] = $this->build($gravatar, $size);
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
        $src = $this->getUrl($source, $size);

        return $src;
    }

    /**
     * {@inheritDoc}
     */
    public function getUrl($email, $size = 80)
    {
        $src = '%s://www.gravatar.com/avatar/%s%s?s=%d&d=%s&r=%s';
        $hash = md5(strtolower($email));
        $options = $this->options;
        $src = sprintf(
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
