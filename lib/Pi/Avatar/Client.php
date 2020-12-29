<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Avatar;

use Pi;

/**
 * Client avatar handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Client extends AbstractAvatar
{
    /**
     * {@inheritDoc}
     */
    public function getSource($uid, $size = 80)
    {
        $uri    = Pi::service('user')->getUrl('avatar', 'get');
        $result = Pi::service('remote')->get(
            $uri,
            [
            'id'   => $uid,
            'size' => $size,
            'html' => 0,
        ]
        );
        $src    = $result['data'];

        return $src;
    }

    /**
     * {@inheritDoc}
     */
    public function getSourceList($uids, $size = 80)
    {
        $uri    = Pi::service('user')->getUrl('avatar', 'mget');
        $result = Pi::service('remote')->get(
            $uri,
            [
            'id'   => $uids,
            'size' => $size,
            'html' => 0,
        ]
        );

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function build($source, $size = '', $uid = null)
    {
        return $source;
    }

    /**
     * Check if a named size is available
     *
     * @param string $size
     *
     * @return bool
     */
    public function hasSize($size)
    {
        return false;
    }
}
