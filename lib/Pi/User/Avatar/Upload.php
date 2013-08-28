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
        $src = Pi::url($this->getRelativePath($uid, $size));

        return $src;
    }

    /**
     * Get relative path
     *
     * @param int $uid
     * @param string $size
     *
     * @return string
     */
    protected function getRelativePath($uid, $size = '')
    {
        $folder = $this->canonizeSize($size, false);
        $avatar = $this->model->avatar;
        $path = sprintf('upload/avatar/%s/%s', $folder, $avatar);

        return $path;
    }
}
