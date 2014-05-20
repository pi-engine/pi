<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Avatar;

use Pi;

/**
 * Local avatar handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Local extends AbstractAvatar
{
    /**
     * {@inheritDoc}
     */
    public function getSource($uid, $size = '')
    {
        $src = $this->build('', $size);

        return $src;
    }

    /**
     * {@inheritDoc}
     */
    public function getSourceList($uids, $size = '')
    {
        $src = $this->build('', $size);
        $result = array_fill_keys($uids, $src);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function build($source, $size = '', $uid = null)
    {
        $identifier = $this->canonizeSize($size, false);
        $root = Pi::path('public/custom/avatar/local');
        if (!is_dir($root)) {
            $root = 'static/avatar/local';
        }
        $path = sprintf('%s/%s.png', $root, $identifier);
        $src = Pi::url($path);

        return $src;
    }
}
