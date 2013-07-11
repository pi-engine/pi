<?php
/**
 * Pi Engine user avatar local class
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

namespace Pi\User\Handler;

use Pi;

class AvatarLocal extends AvatarUpload
{
    /**
     * {@inheritDoc}
     */
    public function getPath($size = null)
    {
        return false;
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
        $path = sprintf('static/avatar/%s.jpg', $folder);
        return $path;
    }
}
