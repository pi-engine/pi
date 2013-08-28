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
        $src = '';
        if (!$uid) {
            return $src;
        }

        $avatar = Pi::user()->get($uid, 'avatar');
        if ($avatar) {
            $src = $this->build($avatar, $size);
        }

        return $src;
    }

    /**
     * {@inheritDoc}
     */
    public function build($source, $size = '')
    {
        $folder = $this->canonizeSize($size, false);
        $path = sprintf('upload/avatar/%s/%s', $folder, $source);
        $src = Pi::url($path);

        return $src;
    }
}
