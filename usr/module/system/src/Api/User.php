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
use Pi\Db\Sql\Where;
use Pi\User\Model\System as UserModel;

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
            'salt'          => array(),
            'email'         => array(),
            'name'          => array(),
            'avatar'        => array(),
            'birthdate'     => array(),
            'gender'        => array(),
            'active'        => array(),
        );

        return $meta;
    }

    /**
     * Get user model
     *
     * @param int       $uid
     * @param string    $field
     *
     * @return UserModel
     */
    public function getUser($uid, $field = 'id')
    {
        $user = new UserModel($uid, $field);

        return $user;
    }

    /**
     * Get user IDs subject to conditions
     *
     * @param array|Where   $condition
     * @param int           $limit
     * @param int           $offset
     * @param string        $order
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

        if ($condition instanceof Where) {
            $where = $condition;
        } else {
            $data = $this->canonizeUser($condition);
            if (!isset($data['active'])) {
                $data['active'] = 1;
            }
            $where = $data;
        }

        $modelAccount = Pi::model('user_account');
        $select = $modelAccount->select();
        $select->columns(array('id'));
        $select->where($where);
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
     * @param array|Where  $condition
     *
     * @return int
     * @api
     */
    public function getCount($condition = array())
    {
        if ($condition instanceof Where) {
            $where = $condition;
        } else {
            $data = $this->canonizeUser($condition);
            if (!isset($data['active'])) {
                $data['active'] = 1;
            }
            $where = $data;
        }

        $modelAccount = Pi::model('user_account');
        $select = $modelAccount->select()->where($where)
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
        $uid = $this->addAccount($data);

        return $uid;
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
        if (!$uid) {
            return false;
        }

        $result = $this->updateAccount($uid, $data);

        return $result;
    }

    /**
     * Delete a user
     *
     * @param   int         $uid
     * @return  bool|null   Null for no-action
     * @api
     */
    public function deleteUser($uid)
    {
        if (!$uid) {
            return false;
        }

        $status = $this->deleteAccount($uid);

        return $status;
    }

    /**
     * Activate a user account
     *
     * @param   int         $uid
     * @return  bool|null   Null for no-action
     * @api
     */
    public function activateUser($uid)
    {
        if (!$uid) {
            return false;
        }

        $status = $this->activateAccount($uid);

        return $status;
    }

    /**
     * Enable a user
     *
     * @param   int     $uid
     *
     * @return  bool|null   Null for no-action
     * @api
     */
    public function enableUser($uid)
    {
        if (!$uid) {
            return false;
        }

        $status = $this->enableAccount($uid);

        return $status;
    }

    /**
     * Disable a user
     *
     * @param   int     $uid
     *
     * @return  bool|null   Null for no-action
     * @api
     */
    public function disableUser($uid)
    {
        if (!$uid) {
            return false;
        }

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
    public function get($uid, $field, $filter = false)
    {
        if (!$uid) {
            return false;
        }

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
        if (!$uid) {
            return false;
        }

        $row = Pi::model('user_account')->find($uid);
        if (!$row) {
            $result = false;
        } else {
            $row->assign(array($field => $value));
            try {
                $row->save();
                $result = true;
            } catch (\Exception $e) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Set user role(s)
     *
     * @param int           $uid
     * @param string|array  $role
     * @param string        $section
     *
     * @return bool
     */
    public function setRole($uid, $role, $section = '')
    {
        if (!$uid) {
            return false;
        }

        if (is_string($role)) {
            $section = $section ?: 'front';
            $role = array(
                $section    => $role,
            );
        }
        $model = Pi::model('user_role');
        $rowset = $model->select(array(
            'uid'       => $uid,
            'section'   => array_keys($role),
        ));
        foreach ($rowset as $row) {
            $row['role'] = $role[$row['section']];
            try {
                $row->save();
            } catch (\Exception $e) {
                return false;
            }
            unset($role[$row['section']]);
        }
        foreach ($role as $section => $roleValue) {
            $row = $model->createRow(array(
                'uid'       => $uid,
                'section'   => $section,
                'role'      => $roleValue,
            ));
            try {
                $row->save();
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get user role
     *
     * Section: `admin`, `front`
     * If section is specified, returns the role;
     * if not, return associative array of roles.
     *
     * @param        $uid
     * @param string $section   Section name: admin, front
     *
     * @return string|array
     */
    public function getRole($uid, $section = '')
    {
        if (!$uid) {
            return false;
        }

        $where = array('uid' => $uid);
        if ($section) {
            $where['section'] = $section;
        }
        $rowset = Pi::model('user_role')->select($where);
        if ($section) {
            $result = $rowset->current()->role;
        } else {
            $result = array();
            foreach ($rowset as $row) {
                $result[$row['section']] = $row['role'];
            }
        }

        return $result;
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
        $row->prepare();
        try {
            $row->save();
        } catch (\Exception $e) {
            return false;
        }

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
        if (!$uid) {
            return false;
        }

        $data = $this->canonizeUser($data);
        $row = Pi::model('user_account')->find($uid);
        if ($row) {
            $row->assign($data);
            if (isset($data['credential'])) {
                $row->prepare();
            }
            try {
                $row->save();
                $status = true;
            } catch (\Exception $e) {
                $status = false;
            }
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
     * @param int $uid
     *
     * @return bool|null   Null for no-action
     */
    public function deleteAccount($uid)
    {
        if (!$uid) {
            return false;
        }

        $model = Pi::model('user_account');
        $row = $model->find($uid);
        if (!$row) {
            return false;
        }
        if ((int) $row['time_deleted'] > 0) {
            return null;
        }
        $row->assign(array(
            'active'        => 0,
            'time_deleted'  => time(),
        ));
        try {
            $row->save();
        } catch (\Exception $e) {
            return false;
        }

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
     * @return bool|null   Null for no-action
     */
    public function activateAccount($uid)
    {
        if (!$uid) {
            return false;
        }

        $model = Pi::model('user_account');
        $row = $model->find($uid);
        if (!$row || (int) $row['time_deleted'] > 0) {
            return false;
        }
        if ((int) $row['time_activated'] > 0) {
            return null;
        }
        $row->assign(array(
            'active'            => 1,
            'time_activated'    => time(),
        ));
        try {
            $row->save();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Enable/disable an account and set `time_disabled` and `active`
     *
     * Deleted accounts are not allowed to enable/disable.
     *
     * Only disabled account can be enabled, set `active` to true
     * and reset `time_disabled`; only enabled account can be disabled,
     * set `active` to false and set `time_disabled`.
     *
     * @param int   $uid
     * @param bool  $flag
     *
     * @return bool|null   Null for no-action
     */
    public function enableAccount($uid, $flag = true)
    {
        if (!$uid) {
            return false;
        }

        $model = Pi::model('user_account');
        $row = $model->find($uid);
        if (!$row
            //|| (int) $row['time_activated'] < 1
            || (int) $row['time_deleted'] > 0
        ) {
            return false;
        }
        if (($flag && (int) $row['time_disabled'] < 0)
            || (!$flag && (int) $row['time_disabled'] > 0)
        ) {
            return null;
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
        try {
            $row->save();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Get a type of field value(s) of a list of user
     *
     * @param int[]|int $uid
     * @param string[]  $fields
     * @param bool      $filter     To filter for display
     * @return array|bool
     * @api
     */
    public function getFields($uid, $fields = array(), $filter = false)
    {
        if (!$uid) {
            return false;
        }

        $result = array();
        $uids = (array) $uid;
        if (!$fields) {
            $fields = array_keys($this->getMeta());
        } else {
            $fields = array_unique($fields);
        }

        $primaryKey = 'id';
        $fields[] = $primaryKey;
        $where = array($primaryKey => $uids);
        $model = Pi::model('user_account');
        $select = $model->select()->where($where)->columns($fields);
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
