<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\User\Resource;

use Pi;

/**
 * User data handler
 *
 * Data APIs:
 *
 * - add($uid, $name, $content, $module, $time)
 * - get($uid, $name)
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Data extends AbstractResource
{
    /**
     * Get user data
     *
     * @param int       $uid
     * @param string    $name
     *
     * @return mixed
     */
    public function get($uid, $name)
    {
        $result = array();

        if (!$this->isAvailable()) {
            return $result;
        }
        $result = Pi::api('user', 'data')->get($uid, $name);

        return $result;
    }

    /**
     * Delete user data
     *
     * @param int       $uid
     * @param string    $name
     *
     * @return bool
     */
    public function delete($uid, $name)
    {
        $result = false;

        if (!$this->isAvailable()) {
            return $result;
        }
        $result = Pi::api('user', 'data')->delete($uid, $name);

        return $result;
    }

    /**
     * Write user data
     *
     * @param int $uid
     * @param string $name
     * @param mixed $data
     * @param string $module
     * @param int $time
     * @return bool
     */
    public function add($uid, $name, $data, $module = '', $time = null)
    {
        if (!$this->isAvailable()) {
            return false;
        }
        $module = $module ?: Pi::service('module')->current();
        $time = $time ?: time();
        $result = Pi::api('user', 'data')->add(
            $uid,
            $name,
            $data,
            $module,
            $time
        );

        return $result;
    }
}
