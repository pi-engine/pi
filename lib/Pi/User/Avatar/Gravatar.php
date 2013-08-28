<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\User\Avatar;

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
            $data = Pi::user()->get($uid, array('avatar', 'email'));
            if ($data) {
                if (false === strpos($data['avatar'], '@')) {
                    $gravatar = $data['email'];
                } else {
                    $gravatar = $data['avatar'];
                }
            }
        }

        $size = $this->canonizeSize($size);
        $src = $this->getUrl($gravatar, $size);

        return $src;
    }

    /**
     * {@inheritDoc}
     */
    public function getUrl($email, $size = 80)
    {
        $src = '%s://www.gravatar.com/avatar/%s%s?s=%d&d=%s&r=%s';
        $hash = md5(strtolower($email));
        $src = sprintf(
            $src,
            !empty($this->options['secure']) ? 'https' : 'http',
            $hash,
            isset($this->options['extension'])
                ? '.' . $this->options['extension'] : '',
            $size,
            isset($this->options['default']) ? $this->options['default'] : 'mm',
            isset($this->options['rate']) ? $this->options['rate'] : 'g'
        );

        return $src;
    }
}
