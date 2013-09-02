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
 * - get($uid, $name, $returnArray)
 * - set($uid, $name, $value, $module, $time)
 * - setInt($uid, $name, $value, $module, $time)
 * - increment($uid, $name, $value, $module, $time)
 * - delete($uid, $name)
 * - find($conditions)
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
     * @return int|mixed|array
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
            $value = (null === $row['value_int'])
                ? $row['value'] : (int) $row['value_int'];
            if (!$returnArray) {
                $result = $value;
            } else {
                $result = array(
                    'time'      => $row['time'],
                    'value'     => $value,
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
        try {
            Pi::model('user_data')->delete($where);
            $result = true;
        } catch (\Exception $e) {
            $result = false;
        }

        return $result;
    }

    /**
     * Write user data
     *
     * @param int|array $uid
     * @param string $name
     * @param mixed|int $value
     * @param string $module
     * @param int $time
     * @return bool
     */
    public function set($uid, $name = null, $value = null, $module = '', $time = null)
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
            'module'    => $module,
            'time'      => $time,
        );
        if (is_int($value)) {
            $vars['value_int'] = $value;
        } else {
            $vars['value'] = $value;
        }

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
        try {
            $row->save();
            $result = true;
        } catch (\Exception $e) {
            $result = false;
        }

        return $result;
    }

    /**
     * Find a data subject to conditions
     *
     * @param array $conditions
     *
     * @return array|bool
     */
    public function find(array $conditions)
    {
        $result = false;
        if (isset($conditions['value']) && is_int($conditions['value'])) {
            $conditions['value_int'] = $conditions['value'];
            unset($conditions['value']);
        }
        $rowset = Pi::model('user_data')->select($conditions);
        $row = $rowset->current();
        if ($row) {
            $result = $row->toArray();
        }

        return $result;
    }

    /**
     * Write user integer data
     *
     * @param int|array $uid
     * @param string $name
     * @param int $value
     * @param string $module
     * @param int $time
     * @return bool
     */
    public function setInt($uid, $name = null, $value = 0, $module = '', $time = null)
    {
        if (is_array($uid) && isset($uid['value'])) {
            $uid['value'] = (int) $uid['value'];
        }
        $value = (int) $value;
        return $this->set($uid, $name, $value, $module, $time);
    }

    /**
     * Increment/decrement an int data
     *
     * Positive to increment or negative to decrement; 0 to reset!
     *
     * @param int    $uid
     * @param string $name
     * @param int    $value
     *
     * @return bool
     */
    public function increment($uid, $name, $value)
    {
        $row = $this->find(array('uid' => $uid, 'name' => $name));
        if (!$row) {
            $result = $this->setInt($uid, $name, $value);
        } else {
            $model = Pi::model('user_data');
            $value = (int) $value;
            if (0 == $value) {
                $string = '`value_int`=0';
            } elseif (0 < $value) {
                $string = '`value_int`=`value_int`+' . $value;
            } else {
                $string = '`value_int`=`value_int`-' . abs($value);
            }
            $sql = 'UPDATE ' . $model->getTable()
                . ' SET ' . $string
                . ' WHERE `uid`=' . $uid
                . ' AND `name`=\'' . $name . '\'';
            try {
                Pi::db()->query($sql);
                $result = true;
            } catch (\Exception $e) {
                $result = false;
            }
        }

        return $result;
    }
}
