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

        $src = $this->build($gravatar, $size);

        return $src;
    }

    /**
     * {@inheritDoc}
     */
    public function build($source, $size = '')
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
        $options = $this->options['gravatar'];
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
