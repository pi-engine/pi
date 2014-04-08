<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\User\Resource;

use Pi;

/**
 * User activity handler
 *
 * Activity APIs:
 *
 *   - get($uid, $name, $limit, $offset)
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Activity extends AbstractResource
{
    /**
     * Get activity log list
     *
     * Returns activity meta data and log list
     *
     * @param int       $uid
     * @param string    $name
     * @param int       $limit
     * @param int       $offset
     *
     * @return array
     */
    public function get($uid, $name, $limit, $offset = 0)
    {
        $result = array();

        if (!$this->isAvailable()) {
            return $result;
        }
        $result = Pi::api('activity', 'user')->get($uid, $name, $limit, $offset);

        return $result;
    }
}
