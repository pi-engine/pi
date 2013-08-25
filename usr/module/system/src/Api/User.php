<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\System\Api;

use Pi;
use Pi\Application\AbstractApi;

/**
 * User account manipulation APIs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class User extends AbstractApi
{
    /** @var string Module name */
    protected $module = 'system';

    /**
     * Get fields specs of specific type and action
     *
     * - Available types: `account`, `profile`, `compound`
     * - Available actions: `display`, `edit`, `search`
     *
     * @param string $type
     * @param string $action
     * @return array
     * @api
     */
    public function getMeta($type = '', $action = '')
    {
        $meta = array(
            'identity'      => array(),
            'credential'    => array(),
        );

        return $meta;
    }

    /**
     * Get user IDs subject to conditions
     *
     * @param array     $condition
     * @param int       $limit
     * @param int       $offset
     * @param string    $order
     * @return int[]
     * @api
     */
    public function getUids(
        $condition  = array(),
        $limit      = 0,
        $offset     = 0,
        $order      = ''
    ) {
        $result = array();

        $data = $this->canonizeUser($condition);
        if (!isset($data['active'])) {
            $data['active'] = 1;
        }

        $modelAccount = Pi::model('user_account');
        $select = $modelAccount->select();
        $dataAccount = $data['account'];
        $select->columns(array('id'));
        $select->where($dataAccount);
        if ($order) {
            $select->order($order);
        }
        if ($limit) {
            $select->limit($limit);
        }
        if ($offset) {
            $select->offset($offset);
        }
        $rowset = $modelAccount->selectWith($select);
        foreach ($rowset as $row) {
            $result[] = (int) $row['id'];
        }

        return $result;
    }

    /**
     * Get user count subject to conditions
     *
     * @param array  $condition
     *
     * @return int
     * @api
     */
    public function getCount($condition = array())
    {
        $data = $this->canonizeUser($condition);
        if (!isset($data['active'])) {
            $data['active'] = 1;
        }

        $modelAccount = Pi::model('user_account');
        $dataAccount = $data['account'];
        $select = $modelAccount->select()->where($dataAccount)
            ->columns(array(
                'count' => Pi::db()->expression('COUNT(*)')
            ));
        $row = $modelAccount->selectWith($select)->current();
        $count = (int) $row['count'];

        return $count;
    }

    /**
     * Add a user with full set of data
     *
     * @param   array   $data
     *
     * @return  int
     * @api
     */
    public function addUser($data)
    {
        $result = array();
        $uid = $this->addAccount($data);

        return $uid;
    }

    public function getUser($uid, $field = 'id')
    {
        $data = array();

    }

    /**
     * Update a user
     *
     * @param   int         $uid
     * @param   array       $data
     *
     * @return  bool
     * @api
     */
    public function updateUser($uid, array $data)
    {
        $result = $this->updateAccount($uid, $data);

        return $result;
    }

    /**
     * Delete a user
     *
     * @param   int         $uid
     * @return  bool
     * @api
     */
    public function deleteUser($uid)
    {
        $status = $this->deleteAccount($uid);

        return $status;
    }

    /**
     * Activate a user account
     *
     * @param   int         $uid
     * @return  bool
     * @api
     */
    public function activateUser($uid)
    {
        $status = $this->activateAccount($uid);

        return $status;
    }

    /**
     * Enable a user
     *
     * @param   int     $uid
     *
     * @return  bool
     * @api
     */
    public function enableUser($uid)
    {
        $status = $this->enableAccount($uid);

        return $status;
    }

    /**
     * Disable a user
     *
     * @param   int     $uid
     *
     * @return  bool
     * @api
     */
    public function disableUser($uid)
    {
        $status = $this->enableAccount($uid, false);

        return $status;
    }

    /**
     * Get field value(s) of a user field(s)
     *
     * @param int|int[]         $uid
     * @param string|string[]   $field
     * @param bool              $filter
     * @return mixed|mixed[]
     * @api
     */
    public function get($uid, $field, $filter = true)
    {
        $result = array();
        $fields   = (array) $field;
        $uids   = (array) $uid;

        $meta   = $this->canonizeMeta($fields);
        $fields = $this->getFields($uids, $meta, $filter);
        foreach ($fields as $id => $data) {
            if (isset($result[$id])) {
                $result[$id] += $data;
            } else {
                $result[$id] = $data;
            }
        }
        if (is_scalar($uid)) {
            $result = isset($result[$uid]) ? $result[$uid] : array();
            if (is_scalar($field)) {
                $result = isset($result[$field]) ? $result[$field] : array();
            }
        } elseif (is_scalar($field)) {
            foreach ($result as $id => &$data) {
                $data = isset($data[$field]) ? $data[$field] : array();
            }
        }

        return $result;
    }

    /**
     * Set value of a user field
     *
     * @param int       $uid
     * @param string    $field
     * @param mixed     $value
     * @return bool
     * @api
     */
    public function set($uid, $field, $value)
    {
        $row = Pi::model('user_account')->find($uid);
        if (!$row) {
            $result = false;
        } else {
            $row->assign(array($field => $value));
            $row->save();
            $result = true;
        }

        return $result;
    }

    /**
     * Increment/decrement a user field
     *
     * Positive to increment or negative to decrement
     *
     * @param int    $uid
     * @param string $field
     * @param int    $value
     *
     * @return bool
     */
    public function increment($uid, $field, $value)
    {
        $model = Pi::model('user_account');
        if ($value > 0) {
            $string = '+' . $value;
        } else {
            $string = '-' . abs($value);
        }
        $sql = 'UPDATE ' . $model->getTable()
            . ' SET `' . $field . '`=`' . $field . '`' . $string
            . ' WHERE `uid`=' . $uid;
        Pi::db()->getAdapter()->query($sql);

        return true;
    }

    /**
     * Canonize profile field list to group by types
     *
     * @param string[] $fields
     *
     * @return array
     */
    public function canonizeMeta(array $fields)
    {
        $meta = array();
        $fieldMeta = $this->getMeta();
        foreach ($fields as $field) {
            if (isset($fieldMeta[$field])) {
                $meta[] = $field;
            }
        }

        return $meta;
    }

    /**
     * Canonize user full set data or for a specific type
     *
     * @param array     $rawData
     * @return array
     */
    public function canonizeUser(array $rawData)
    {
        $result = array();

        $fields = $this->getMeta();
        foreach ($rawData as $key => $value) {
            if (isset($fields[$key])) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Add account data and generate uid, set `time_created`
     *
     * @param array $data
     *
     * @return int
     */
    public function addAccount(array $data)
    {
        $data = $this->canonizeUser($data);
        if (!isset($data['time_created'])) {
            $data['time_created'] = time();
        }
        $row = Pi::model('user_account')->createRow($data);
        $row->save();

        return (int) $row['id'];
    }

    /**
     * Update user account data
     *
     * @param int $uid
     * @param array $data
     *
     * @return bool
     */
    public function updateAccount($uid, array $data)
    {
        $data = $this->canonizeUser($data);
        $row = Pi::model('user_account')->find($uid);
        if ($row) {
            $row->assign($data);
            //if (isset($data['credential']))
            $row->save();
            $status = true;
        } else {
            $status = false;
        }

        return $status;
    }

    /**
     * Delete an account and set `active` to false and set `time_deleted`
     *
     * The action is only allowed to perform once
     *
     * @param $uid
     *
     * @return bool
     */
    public function deleteAccount($uid)
    {
        $model = Pi::model('user_account');
        $row = $model->find($uid);
        if (!$row || (int) $row['time_deleted'] > 0) {
            return false;
        }
        $row->assign(array(
            'active'        => 0,
            'time_deleted'  => time(),
        ));
        $row->save();

        return true;
    }

    /**
     * Activate an account and set `time_activated`
     *
     * Only non-activated and not deleted user can be activated;
     * an account is not allowed to deactivate.
     *
     * @param int $uid
     *
     * @return bool
     */
    public function activateAccount($uid)
    {
        $model = Pi::model('user_account');
        $row = $model->find($uid);
        if (!$row
            || (int) $row['time_activated'] > 0
            || (int) $row['time_deleted'] > 0
        ) {
            return false;
        }
        $row->assign(array(
            'active'            => 1,
            'time_activated'    => time(),
        ));
        $row->save();

        return true;
    }

    /**
     * Enable/disable an account and set `time_disabled` and `active`
     *
     * Non-activated and deleted accounts are not allowed to enable/disable.
     *
     * Only disabled account can be enabled, set `active` to true
     * and reset `time_disabled`; only enabled account can be disabled,
     * set `active` to false and set `time_disabled`.
     *
     * @param int   $uid
     * @param bool  $flag
     *
     * @return bool
     */
    public function enableAccount($uid, $flag = true)
    {
        $model = Pi::model('user_account');
        $row = $model->find($uid);
        if (!$row
            || (int) $row['time_activated'] < 1
            || (int) $row['time_deleted'] > 0
        ) {
            return false;
        }
        if (($flag && (int) $row['time_disabled'] < 0)
            || (!$flag && (int) $row['time_disabled'] > 0)
        ) {
            return false;
        }
        if ($flag) {
            $data = array(
                'active'            => 1,
                'time_disabled'     => 0,
            );
        } else {
            $data = array(
                'active'            => 0,
                'time_disabled'     => time(),
            );
        }
        $row->assign($data);
        $row->save();

        return true;
    }

    /**
     * Get a type of field value(s) of a list of user
     *
     * @param int[]|int $uid
     * @param string[]  $fields
     * @param bool      $filter     To filter for display
     * @return array
     * @api
     */
    public function getFields($uid, $fields = array(), $filter = true)
    {
        $result = array();
        $uids = (array) $uid;
        if (!$fields) {
            $fields = array_keys($this->getMeta());
        } else {
            $fields = array_unique($fields);
        }

        $primaryKey = 'id';
        $fields[] = $primaryKey;
        $model = Pi::model('user_account');
        $select = $model->select()->where(array($primaryKey => $uids))
            ->columns($fields);
        $rowset = $model->selectWith($select);
        foreach ($rowset as $row) {
            $id = (int) $row[$primaryKey];
            if ($filter) {
                $result[$id] = $row->filter($fields);
            } else {
                $result[$id] = $row->toArray();
            }
        }
        if (is_scalar($uid)) {
            if (isset($result[$uid])) {
                $result = $result[$uid];
            } else {
                $result = array();
            }
        }

        return $result;
    }
}
