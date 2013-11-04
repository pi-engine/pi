<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
        /*
        $src = Pi::service('user')->getUrl('avatar', array(
            'id'    => $uid,
            'size'  => $size
        ));
        */
        $uri = Pi::service('user')->getUrl('avatar', 'get');
        $result = Pi::service('remote')->get($uri, array(
            'id'    => $uid,
            'size'  => $size
        ));
        $src = $result['data'];

        return $src;
    }

    /**
     * {@inheritDoc}
     */
    public function getSourceList($uids, $size = 80)
    {
        //$result = array();
        /*
        $_this = $this;
        array_walk($uids, function ($uid) use (&$result, $size, $_this) {
            $result[$uid] = $_this->getSource($uid, $size);
        });
        */

        $uri = Pi::service('user')->getUrl('avatar', 'mget');
        $result = Pi::service('remote')->get($uri, array(
            'id'    => $uids,
            'size'  => $size
        ));

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function build($source, $size = '', $uid = null)
    {
        return $source;
    }
}
