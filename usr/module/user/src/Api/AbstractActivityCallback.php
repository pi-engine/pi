<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Api;

use Pi\Application\Api\AbstractApi;

/**
 * User activity callback
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractActivityCallback extends AbstractApi
{
    /**
     * Get message list of an activity
     *
     * @param int   $uid
     * @param int   $limit
     * @param int   $offset
     *
     * @return array
     */
    abstract public function get($uid, $limit, $offset = 0);
}
