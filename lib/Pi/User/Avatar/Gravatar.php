<?php
/**
 * Pi Engine user avatar gravatar class
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\User
 */

namespace Pi\User\Avatar;

use Pi;

class Gravatar extends AbstractAvatar
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
