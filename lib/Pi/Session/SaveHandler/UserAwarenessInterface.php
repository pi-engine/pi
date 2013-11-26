<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Session\SaveHandler;

/**
 * User awareness interface
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
interface UserAwarenessInterface
{
    /**
     * Set current user id
     *
     * @param int $uid
     *
     * @return bool
     */
    public function setUser($uid);

    /**
     * Kill a user session
     *
     * @param int $uid
     *
     * @return bool|null true for success, false for fail, null for no action
     */
    public function killUser($uid);
}
