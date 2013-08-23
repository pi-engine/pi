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

/**
 * Gravatar handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class AvatarGravatar extends AbstractAvatar
{
    /**
     * {@inheritDoc}
     */
    public function build($size = 80)
    {
        $size = $this->canonizeSize($size);

        $avatar = $this->model->avatar;
        if (false === strpos('@', $avatar)) {
            $avatar = $this->model->email;
        }

        $src = $this->getUrl($avatar, $size);

        return $src;
    }

    /**
     * {@inheritDoc}
     * @return bool
     */
    public function getPath($size = null)
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getUrl($email, $size = 80)
    {
        $src = 'http://www.gravatar.com/avatar/%s?s=%d&d=mm&r=g';
        $hash = md5(strtolower($email));
        $src = sprintf($src, $hash, $size);

        return $src;
    }

    /**
     * Canonize sie
     * @param string|int $size
     * @return string
     */
    protected function canonizeSize($size)
    {
        if (!is_int($size)) {
            switch ($size) {
                case 'mini':
                    $size = 16;
                    break;
                case 'xsmall':
                    $size = 20;
                    break;
                case 'medium':
                    $size = 60;
                    break;
                case 'large':
                    $size = 100;
                    break;
                case 'xlarge':
                    $size = 120;
                    break;
                case 'xxlarge':
                    $size = 150;
                    break;
                case 'normal':
                default:
                    $size = 80;
                    break;
            }
        }

        return $size;
    }

}
