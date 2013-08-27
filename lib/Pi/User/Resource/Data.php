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
     * @param int    $uid
     * @param string $name
     * @param bool   $returnArray
     *
     * @return mixed|array
     */
    public function get($uid, $name, $returnArray = false)
    {
        $result = false;
        $where = array(
            'uid'   => (int) $uid,
            'name'  => $name,
        );
        $row = Pi::model('user_data')->select($where)->current();
        if ($row) {
            if (!$returnArray) {
                $result = $row['content'];
            } else {
                $result = array(
                    'time'      => $row['time'],
                    'content'   => $row['content'],
                    'module'    => $row['module'],
                );
            }
        }

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
        $where = array(
            'uid'   => (int) $uid,
            'name'  => $name,
        );
        Pi::model('user_data')->delete($where);

        return true;
    }

    /**
     * Write user data
     *
     * @param int|array $uid
     * @param string $name
     * @param mixed $content
     * @param string $module
     * @param int $time
     * @return bool
     */
    public function add($uid, $name = null, $content = null, $module = '', $time = null)
    {
        if (is_array($uid)) {
            $id = isset($uid['uid']) ? (int) $uid['uid'] : 0;
            extract($uid);
            $uid = $id;
        }
        $module = $module ?: Pi::service('module')->current();
        $time = $time ?: time();
        $vars = array(
            'uid'       => (int) $uid,
            'name'      => $name,
            'content'   => $content,
            'module'    => $module,
            'time'      => $time,
        );

        $where = array(
            'uid'   => (int) $uid,
            'name'  => $name,
        );
        $row = Pi::model('user_data')->select($where)->current();
        if ($row) {
            $row->assign($vars);
        } else {
            $row = Pi::model('user_data')->createRow($vars);
        }
        $row->save();

        return true;
    }
}
