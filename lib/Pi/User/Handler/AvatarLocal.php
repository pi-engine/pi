<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\User\Handler;

use Pi;

/**
 * Local avatar handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
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
