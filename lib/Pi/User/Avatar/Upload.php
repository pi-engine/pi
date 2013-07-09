<?php
/**
 * Pi Engine user avatar upload class
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

class Upload extends AbstractAvatar
{
    /**
     * {@inheritDoc}
     */
    public function build($size = '')
    {
        $src = Pi::url($this->getRelativePath($size));
        return $src;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath($size = null)
    {
        if (null === $size) {
            $path = array();
            foreach (array('mini', 'xsmall', 'medium', 'normal', 'large', 'xlarge', 'xxlarge') as $key) {
                $path[$key] = Pi::Path($this->getRelativePath($key));
            }
        } else {
            $path = Pi::Path($this->getRelativePath($size));
        }

        return $path;
    }

    /**
     * Get relative path
     *
     * @param string $size
     * @return string
     */
    protected function getRelativePath($size = '')
    {
        $folder = $this->canonizeSize($size);
        $avatar = $this->model->avatar;
        $path = sprintf('upload/avatar/%s/%s', $folder, $avatar);
        return $path;
    }

    /**
     * Canonize sie
     * @param string $size
     * @return string
     */
    protected function canonizeSize($size)
    {
        switch ($size) {
            case 'mini':
            case 'xsmall':
            case 'medium':
            case 'large':
            case 'xlarge':
            case 'xxlarge':
                $folder = $size;
                break;
            case 'o':
            case 'original':
                $folder = 'original';
                break;
            case 'normal':
            default:
                $folder = 'normal';
                break;
        }
        return $folder;
    }
}
