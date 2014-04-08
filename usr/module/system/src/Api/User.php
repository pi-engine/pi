<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Api;

use Pi;
use Module\System\Api\AbstractUser as AbstractUseApi;
use Pi\Db\Sql\Where;
use Pi\User\Model\System as UserModel;

/**
 * User account manipulation APIs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class User extends AbstractUseApi
{
    /** @var string Route for user URLs */
    protected $route = 'sysuser';

    /** @var string Module name */
    protected $module = 'system';

    /**
     * Get fields specs of specific type and action
     *
     * @param string $type      Not used
     * @param string $action    Not used
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

            'time_created'      => array(),
            'time_activated'    => array(),
            'time_disabled'     => array(),
            'time_deleted'      => array(),
        );

        return $meta;
    }

    /**
     * Get user model
     *
     * @param int|string|array $uid
     * @param string    $field
     *
     * @return UserModel|null
     */
    public function getUser($uid, $field = 'id')
    {
        $user = new UserModel($uid, $field);
        if ($uid && is_scalar($uid) && !$user->id) {
            $user = null;
        }

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
     * {@inheritDoc}
     */
    public function getList(
        $condition  = array(),
        $limit      = 0,
        $offset     = 0,
        $order      = '',
        $field      = array()
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
        /*
        $select = $modelAccount->select()->where($where)
            ->columns(array(
                'count' => Pi::db()->expression('COUNT(*)')
            ));
        $row = $modelAccount->selectWith($select)->current();
        $count = (int) $row['count'];
        */
        $count = $modelAccount->count($where);

        return $count;
    }

    /**
     * Add a user with full set of data
     *
     * @param   array   $data
     * @param   bool    $setRole
     *
     * @return  int
     * @api
     */
    public function addUser($data, $setRole = true)
    {
        return parent::addUser($data, $setRole);
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
        return parent::updateUser($uid, $data);
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
        return parent::deleteUser($uid);
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
        return parent::activateUser($uid);
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
        return parent::enableUser($uid);
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
        return parent::disableUser($uid);
    }

    /**
     * Get field value(s) of a user field(s)
     *
     * @param int|int[]         $uid
     * @param string|string[]   $field
     * @param bool              $filter
     * @param bool              $activeOnly
     *
     * @return mixed|mixed[]
     * @api
     */
    public function get(
        $uid,
        $field      = array(),
        $filter     = false,
        $activeOnly = false
    ) {
        if (!$uid) {
            return false;
        }

        $result = array();
        $uids   = (array) $uid;
        $fields   = $field ? (array) $field : array_keys($this->getMeta());

        /*
        $activeMarked = false;
        if ($activeOnly && !in_array('active', $fields)) {
            $fields[] = 'active';
            $activeMarked = true;
        }
        */

        $meta   = $this->canonizeField($fields);
        $fields = $this->getFields($uids, $meta, $filter, $activeOnly);
        foreach ($fields as $id => $data) {
            if (isset($result[$id])) {
                $result[$id] += $data;
            } else {
                $result[$id] = $data;
            }
        }

        /*
        if ($activeOnly) {
            foreach (array_keys($result) as $id) {
                if (empty($result[$id]['active'])) {
                    unset($result[$id]);
                } elseif ($activeMarked) {
                    unset($result[$id]['active']);
                }
            }
        }
        */

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
     * Get field value(s) of users
     *
     * @param int[]             $uids
     * @param string|string[]   $field
     * @param bool              $filter
     * @param bool              $activeOnly
     *
     * @return mixed[]
     * @api
     */
    public function mget(
        array $uids,
        $field = array(),
        $filter = false,
        $activeOnly = false
    ) {
        $result = $this->get($uids, $field, $filter, $activeOnly);

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
        $uid = (int) $uid;
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
     *
     * @return bool
     */
    public function setRole($uid, $role)
    {
        return parent::setRole($uid, $role);
    }

    /**
     * Revoke user role(s)
     *
     * @param int          $uid
     * @param string|array $role
     *
     * @return bool
     */
    public function revokeRole($uid, $role)
    {
        return parent::revokeRole($uid, $role);
    }

    /**
     * Get user role
     *
     * @param int    $uid
     * @param string $section   Section name: admin, front
     *
     * @return string|array
     */
    public function getRole($uid, $section = '')
    {
        return parent::getRole($uid, $section);
    }

    /**
     * Canonize profile field list to group by types
     *
     * @param string[] $fields
     *
     * @return array
     */
    public function canonizeField(array $fields)
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
     * @param string    $type
     * @return array
     */
    public function canonizeUser(array $rawData, $type = '')
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
        return parent::addAccount($data);
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
        return parent::updateAccount($uid, $data);
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
        return parent::deleteAccount($uid);
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
        return parent::activateAccount($uid);
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
        return parent::enableAccount($uid, $flag);
    }

    /**
     * Get a type of field value(s) of a list of user
     *
     * @param int[]|int $uid
     * @param string[]  $fields
     * @param bool      $filter     To filter for display
     * @param bool $activeOnly
     *
     * @return array|bool
     * @api
     */
    public function getFields(
        $uid,
        $fields = array(),
        $filter = false,
        $activeOnly = false
    ) {
        if (!$uid) {
            return false;
        }

        $result = array();
        $uids   = (array) $uid;
        if (!$fields) {
            $fields = array_keys($this->getMeta());
        } else {
            $fields = array_unique($fields);
        }

        $primaryKey = 'id';
        $fields[] = $primaryKey;
        $where = array($primaryKey => $uids);
        if ($activeOnly) {
            $where['active'] = 1;
        }
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
        } else {
            $sorted = array();
            foreach ($uid as $id) {
                $sorted[$id] = $result[$id];
            }
            $result = $sorted;
        }

        return $result;
    }
}
