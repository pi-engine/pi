<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Api;

use Pi;
use Module\System\Api\AbstractUser as AbstractUseApi;
use Pi\Db\Sql\Where;
use Pi\User\Model\Local as UserModel;

/**
 * User account manipulation APIs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class User extends AbstractUseApi
{
    /**
     * @{inheritDoc}
     */
    protected $module = 'user';

    /** @var string Route for user URLs */
    protected $route = 'user';

    /**
     * Get fields specs of specific type and action
     *
     * - Available types: `account`, `profile`, `compound`, `custom`
     * - Available actions: `display`, `edit`, `search`
     *
     * @param string $type
     * @param string $action
     * @return array
     * @api
     */
    public function getMeta($type = '', $action = '')
    {
        $meta = Pi::registry('field', 'user')->read($type, $action);

        return $meta;
    }

    /**
     * Get user model
     *
     * @param int|string $uid
     * @param string    $field
     *
     * @return UserModel
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
     * Usage
     *
     * ```
     *  Pi::service('user')->getUids(
     *      array('location' => 'beijing', 'active' => 1),
     *      10,
     *      0,
     *      array('time_created' => 'desc', 'fullname')
     *  );
     * ```
     *
     * @fixme: `$order` should be prefixed with type if multi-type involved
     *
     * @param array|Where   $condition
     * @param int           $limit
     * @param int           $offset
     * @param string|array  $order
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
            $isJoin = true;
            $data = array();
        } else {
            $isJoin = false;

            $data = $this->canonizeUser($condition);
            if (!isset($data['account']['active'])) {
                $data['account']['active'] = 1;
            }
            if (isset($data['profile'])) {
                $isJoin = true;
            }
        }

        $modelAccount = Pi::model('account', 'user');
        // Single table query
        if (!$isJoin) {
            $select = $modelAccount->select();
            $select->columns(array('id'));
            $dataAccount = $data['account'];
            $select->where($dataAccount);
            if ($order) {
                $select->order($order);
            }
        // Account and profile
        } else {
            $select = Pi::db()->select();
            $select->from(array('account' => $modelAccount->getTable()));
            $select->columns(array('id'));

            $modelProfile = Pi::model('profile', 'user');
            $select->join(
                array('profile' => $modelProfile->getTable()),
                'profile.uid=account.id',
                array()
            );

            if ($condition instanceof Where) {
                $where = $condition;
                if ($order) {
                    $select->order($order);
                }
            } else {
                $canonizeColumn = function ($data, $type) {
                    $result = array();
                    foreach ($data as $col => $val) {
                        $result[$type . '.' . $col] = $val;
                    }
                    return $result;
                };
                $where = $canonizeColumn($data['account'], 'account');
                $where = array_merge($where, $canonizeColumn($data['profile'], 'profile'));

                if ($order) {
                    if (is_array($order)) {
                        $fields = Pi::registry('field', 'user')->read('', 'search');
                        $result = array();
                        foreach ($order as $key => $val) {
                            if (is_string($key)) {
                                if (isset($fields[$key])) {
                                    $key = $fields[$key]['type'] . '.' . $key;
                                    $result[$key] = $val;
                                }
                            } else {
                                if (isset($fields[$val])) {
                                    $val = $fields[$val]['type'] . '.' . $val;
                                    $result[$key] = $val;
                                }
                            }
                        }
                        $order = $result;
                    }
                    $select->order($order);
                }
            }

            $select->where($where);
        }

        if ($limit) {
            $select->limit($limit);
        }
        if ($offset) {
            $select->offset($offset);
        }
        if (!$isJoin) {
            $rowset = $modelAccount->selectWith($select);
        } else {
            $rowset = Pi::db()->query($select);
        }
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
        $uids = $this->getUids(
            $condition,
            $limit,
            $offset,
            $order
        );
        $result = array();
        if ('id' == $field[0] && 1 == count($field)) {
            array_walk($uids, function ($uid) use (&$result) {
                $result[$uid] = array('id' => $uid);
            });
        } elseif ($uids) {
            $result = $this->get($uids, $field);
        }

        return $result;
    }

    /**
     * Get user count subject to conditions
     *
     * @param array|Where   $condition
     *
     * @return int
     * @api
     */
    public function getCount($condition = array())
    {
        if ($condition instanceof Where) {
            $isJoin = true;
            $data = array();
        } else {
            $isJoin = false;

            $data = $this->canonizeUser($condition);
            if (!isset($data['account']['active'])) {
                $data['account']['active'] = 1;
            }
            if (isset($data['profile'])) {
                $isJoin = true;
            }
        }

        $modelAccount = Pi::model('account', 'user');
        // Single table query
        if (!$isJoin) {
            $select = $modelAccount->select();
            $select->columns(array('count' => Pi::db()->expression('COUNT(*)')));
            $select->where($data['account']);
            // Account and profile
        } else {
            $select = Pi::db()->select();
            $select->from(array('account' => $modelAccount->getTable()));
            $select->columns(array('count' => Pi::db()->expression('COUNT(*)')));

            $modelProfile = Pi::model('profile', 'user');
            $select->join(
                array('profile' => $modelProfile->getTable()),
                'profile.uid=account.id',
                array()
            );

            if ($condition instanceof Where) {
                $where = $condition;
            } else {
                $canonizeColumn = function ($data, $type) {
                    $result = array();
                    foreach ($data as $col => $val) {
                        $result[$type . '.' . $col] = $val;
                    }
                    return $result;
                };
                $where = $canonizeColumn($data['account'], 'account');
                $where = array_merge($where, $canonizeColumn($data['profile'], 'profile'));
            }

            $select->where($where);
        }
        if (!$isJoin) {
            $row = $modelAccount->selectWith($select)->current();
        } else {
            $row = Pi::db()->query($select)->current();
        }
        $count = (int) $row['count'];

        return $count;
    }

    /**
     * Add a user with full set of data
     *
     * Full procedure:
     *
     * - Add account data and get uid
     * - Add profile data
     * - Add compound data, multiple, if any
     * - Add custom data, multiple, if any
     *
     * @param   array   $data
     * @param   bool    $setRole
     *
     * @return  int|array uid or uid and error of account/profile/compound
     * @api
     */
    public function addUser($data, $setRole = true)
    {
        $error = array();
        $uid = parent::addUser($data, $setRole);

        if (!$uid) {
            $error[] = 'account';
        } else {
            $status = $this->addProfile($uid, $data);
            if (!$status) {
                $error[] = 'profile';
            }
            $status = $this->addCompound($uid, $data);
            if (!$status) {
                $error[] = 'compound';
            }
            /*
            $status = $this->addCustom($uid, $data);
            if (!$status) {
                $error[] = 'custom';
            }
            */
        }

        return $error ? array($uid, $error) : $uid;
    }

    /**
     * Update a user
     *
     * @param   int         $uid
     * @param   array       $data
     *
     * @return  bool|string[]
     * @api
     */
    public function updateUser($uid, array $data)
    {
        if (!$uid) {
            return false;
        }

        $error = array();
        $status = parent::updateUser($uid, $data);
        if (!$status) {
            $error[] = 'account';
        }
        $status = $this->updateProfile($uid, $data);
        if (!$status) {
            $error[] = 'profile';
        }
        $status = $this->updateCompound($uid, $data);
        if (!$status) {
            $error[] = 'compound';
        }
        /*
        $status = $this->updateCustom($uid, $data);
        if (!$status) {
            $error[] = 'custom';
        }
        */

        return $error ? $error : true;
    }

    /**
     * Delete a user
     *
     * @param   int         $uid
     * @return  bool|null|string[] Null for no-action
     * @api
     */
    public function deleteUser($uid)
    {
        if (!$uid) {
            return false;
        }

        $error = array();
        $result = parent::deleteUser($uid);
        if (false === $result) {
            $error[] = 'account';
        }
        $status = $this->deleteProfile($uid);
        if (!$status) {
            $error[] = 'profile';
        }
        $status = $this->deleteCompound($uid);
        if (!$status) {
            $error[] = 'compound';
        }
        /*
        $status = $this->deleteCustom($uid);
        if (!$status) {
            $error[] = 'custom';
        }
        */

        return $error ? $error : $result;
    }

    /**
     * Activate a user account
     *
     * @param   int         $uid
     * @return  bool|null Null for no-action
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
     * @return  bool|null Null for no-action
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
     * @return  bool|null Null for no-action
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
        $fields = $field
            ? (array) $field
            : array_keys($this->getMeta('', 'display'));
        $meta   = $this->canonizeField($fields);
        if ($activeOnly) {
            $activeMarked = false;
            if (!isset($meta['account'])) {
                $meta['account'] = array('active');
                $activeMarked = true;
            }
            $fields = $this->getFields(
                $uids,
                'account',
                $meta['account'],
                $filter,
                $activeOnly
            );
            $uids = array();
            foreach ($fields as $id => $data) {
                if ($activeMarked) {
                    unset($data['active']);
                }
                $result[$id] = $data;
                $uids[] = $id;
            }
            unset($meta['account']);
        }
        if ($uids) {
            foreach ($meta as $type => $fields) {
                $fields = $this->getFields($uids, $type, $fields, $filter, $activeOnly);
                foreach ($fields as $id => $data) {
                    if (isset($result[$id])) {
                        $result[$id] = array_merge($result[$id], (array) $data);
                    } else {
                        $result[$id] = (array) $data;
                    }
                }
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
        $field      = array(),
        $filter     = false,
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
        if (!$uid) {
            return false;
        }

        $fieldMeta = Pi::registry('field', 'user')->read();
        if (isset($fieldMeta[$field])) {
            $type = $fieldMeta[$field]['type'];
            $result = $this->setTypeField($uid, $type, $field, $value);
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * Set user role(s)
     *
     * @param int          $uid
     * @param string|array $role
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
     * @param int       $uid
     * @param string    $section   Section name: admin, front
     *
     * @return array
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
                $meta[$fieldMeta[$field]['type']][] = $field;
            }
        }

        return $meta;
    }

    /**
     * Canonize compound field data
     *
     * Canonize single set:
     * from
     * ````
     *  // Raw data
     *  $rawData = array(<field-name> => <field-value>, <...>);
     *  // Canonized
     *  $compound = array(
     *      array(
     *          'uid'       => <uid>,
     *          'compound'  => <compound>,
     *          'field'     => <field-name>,
     *          'set'       => <set-value>,
     *          'value'     => <field-value>
     *      ),
     *      <...>,
     *  );
     * ````
     *
     * Canonize multi-set:
     * ````
     *  // Raw data
     *  $rawData = array(
     *      array(<field-name> => <field-value>, <...>),
     *      <...>,
     *  );
     *  // Canonized
     *  $compound = array(
     *      array(
     *          'uid'       => <uid>,
     *          'compound'  => <compound>,
     *          'field'     => <field-name>,
     *          'set'       => <set-value>,
     *          'value'     => <field-value>
     *      ),
     *      <...>,
     *  );
     * ````
     *
     * @param int       $uid
     * @param string    $compound
     * @param array     $rawData
     * @param int       $set
     *
     * @return array
     */
    public function canonizeCompound(
        $uid,
        $compound,
        array $rawData,
        $set = 0
    ) {
        $meta = Pi::registry('compound_field', 'user')->read($compound);
        $canonizeSet = function ($data, $set) use ($uid, $compound, $meta) {
            $result = array();
            foreach (array_keys($data) as $key) {
                if (!isset($meta[$key])) {
                    unset($data[$key]);
                    continue;
                }
                $result[] = array(
                    'uid'       => $uid,
                    'compound'  => $compound,
                    'field'     => $key,
                    'set'       => $set,
                    'value'     => $data[$key],
                );
            }

            return $result;
        };

        $result = array();
        if (is_int(key($rawData))) {
            $set = 0;
            foreach ($rawData as $data) {
                $result = array_merge($result, $canonizeSet($data, $set));
                $set++;
            }
        } else {
            $result = $canonizeSet($rawData, $set);
        }

        return $result;
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

        $meta = $this->getMeta($type);
        foreach ($rawData as $key => $value) {
            if (isset($meta[$key])) {
                if ($type) {
                    $result[$key] = $value;
                } else {
                    $result[$meta[$key]['type']][$key] = $value;
                }
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
     * @param $uid
     *
     * @return bool|null  False for erroneous result; Null for no-action
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
     * @return bool|null  False for erroneous result; Null for no-action
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
     * @return bool|null  False for erroneous result; Null for no-action
     */
    public function enableAccount($uid, $flag = true)
    {
        return parent::enableAccount($uid, $flag);
    }

    /**
     * Add user custom profile
     *
     * @param int   $uid
     * @param array $data
     *
     * @return bool
     */
    public function addProfile($uid, array $data)
    {
        if (!$uid) {
            return false;
        }

        $type = 'profile';
        $data = $this->canonizeUser($data, $type);
        $custom = array();
        $meta = $this->getMeta($type);
        foreach ($data as $field => $value) {
            if (!empty($meta[$field]['handler'])) {
                $custom[$field] = $value;
                unset($data[$field]);
            }
        }
        $this->addCustom($uid, $custom, $type);

        $data['uid'] = $uid;
        $model = Pi::model($type, 'user');
        $row = $model->createRow($data);
        try {
            $row->save();
            $status = true;
        } catch (\Exception $e) {
            $status = false;
        }

        return $status;
    }

    /**
     * Update custom profile fields
     *
     * @param int   $uid
     * @param array $data
     *
     * @return bool
     */
    public function updateProfile($uid, array $data)
    {
        if (!$uid) {
            return false;
        }

        $type = 'profile';
        $data = $this->canonizeUser($data, $type);
        $custom = array();
        $meta = $this->getMeta($type);
        foreach ($data as $field => $value) {
            if (!empty($meta[$field]['handler'])) {
                $custom[$field] = $value;
                unset($data[$field]);
            }
        }
        $this->updateCustom($uid, $custom, $type);

        $model = Pi::model($type, 'user');
        $row = $model->find($uid, 'uid');
        $row->assign($data);
        try {
            $row->save();
            $status = true;
        } catch (\Exception $e) {
            $status = false;
        }

        return $status;
    }

    /**
     * Delete custom fields of a user
     *
     * @param $uid
     *
     * @return bool
     */
    public function deleteProfile($uid)
    {
        if (!$uid) {
            return false;
        }

        if (Pi::service('user')->isRoot($uid)) {
            return false;
        }

        $type = 'profile';
        try {
            Pi::model($type, 'user')->delete(array('uid' => $uid));
            $status = true;
        } catch (\Exception $e) {
            $status = false;
        }
        $this->deleteCustom($uid, $type);

        return $status;
    }

    /**
     * Add user compound profile
     *
     * @param int   $uid
     * @param array $data
     *
     * @return bool
     */
    public function addCompound($uid, array $data)
    {
        if (!$uid) {
            return false;
        }

        $type = 'compound';
        $data = $this->canonizeUser($data, $type);
        $model = Pi::model($type, 'user');
        $custom = array();
        $meta = $this->getMeta($type);
        foreach ($data as $compound => $value) {
            if (!empty($meta[$compound]['handler'])) {
                $custom[$compound] = $value;
                continue;
            }
            $compoundSet = $this->canonizeCompound($uid, $compound, $value);
            foreach ($compoundSet as $field) {
                $row = $model->createRow($field);
                try {
                    $row->save();
                } catch (\Exception $e) {
                    return false;
                }
            }
        }
        $this->addCustom($uid, $custom, $type);

        return true;
    }

    /**
     * Update compound fields
     *
     * @param int   $uid
     * @param array $data
     *
     * @return bool
     */
    public function updateCompound($uid, array $data)
    {
        if (!$uid) {
            return false;
        }

        $type = 'compound';
        $data = $this->canonizeUser($data, $type);
        $custom = array();
        $meta = $this->getMeta($type);
        foreach ($data as $compound => $value) {
            if (!empty($meta[$compound]['handler'])) {
                $custom[$compound] = $value;
                continue;
            }
            $result = $this->setCompoundField($uid, $compound, $value);
            if (!$result) {
                return false;
            }
        }
        $this->updateCustom($uid, $custom, $type);

        return true;
    }

    /**
     * Delete all compound fields
     *
     * @param $uid
     *
     * @return bool
     */
    public function deleteCompound($uid)
    {
        if (!$uid) {
            return false;
        }

        if (Pi::service('user')->isRoot($uid)) {
            return false;
        }

        $type = 'compound';
        try {
            Pi::model($type, 'user')->delete(array('uid' => $uid));
            $status = true;
        } catch (\Exception $e) {
            $status = false;
        }
        if ($status) {
            $status = $this->deleteCustom($uid, $type);
        }

        return $status;
    }

    /**
     * Add user custom compound profile
     *
     * @param int   $uid
     * @param mixed $data
     * @param string $type
     *
     * @return bool
     */
    public function addCustom($uid, $data, $type)
    {
        if (!$uid) {
            return false;
        }

        //$type = 'compound';
        $meta = $this->getMeta($type);
        foreach ($data as $field => $valueSet) {
            if (!isset($meta[$field]) || empty($meta[$field]['handler'])) {
                continue;
            }
            $handler = new $meta[$field]['handler']($field);
            $handler->add($uid, $valueSet);
        }

        return true;
    }

    /**
     * Update custom compound fields
     *
     * @param int   $uid
     * @param mixed $data
     * @param string $type
     *
     * @return bool
     */
    public function updateCustom($uid, $data, $type)
    {
        if (!$uid) {
            return false;
        }

        //$type = 'compound';
        $meta = $this->getMeta($type);
        foreach ($data as $field => $valueSet) {
            if (!isset($meta[$field]) || empty($meta[$field]['handler'])) {
                continue;
            }
            $handler = new $meta[$field]['handler']($field);
            $handler->update($uid, $valueSet);
        }

        return true;
    }

    /**
     * Delete all custom compound fields
     *
     * @param int $uid
     * @param string $type
     *
     * @return bool
     */
    public function deleteCustom($uid, $type)
    {
        if (!$uid) {
            return false;
        }

        //$type = 'compound';
        $meta = $this->getMeta($type);
        foreach ($meta as $field => $spec) {
            if (empty($spec['handler'])) {
                continue;
            }
            $handler = new $spec['handler']($field);
            $handler->delete($uid);
        }

        return true;
    }

    /**
     * Get a type of field value(s) of a list of user
     *
     * @param int[]|int $uid
     * @param string    $type
     * @param string[]  $fields
     * @param bool      $filter     To filter for display
     * @param bool      $activeOnly
     *
     * @return array|bool
     * @api
     */
    public function getFields(
        $uid,
        $type,
        $fields     = array(),
        $filter     = false,
        $activeOnly = false
    ) {
        if (!$uid) {
            return false;
        }

        $result = array();
        $uids = (array) $uid;
        if (!$fields) {
            $fields = array_keys($this->getMeta($type, 'display'));
        } else {
            $fields = array_unique($fields);
        }

        if ('account' == $type || 'profile' == $type) {
            $meta = $this->getMeta($type);
            $pFields = array();
            $mFields = array();
            foreach ($fields as $field) {
                if (!isset($meta[$field])
                    || empty($meta[$field]['handler'])
                ) {
                    $pFields[] = $field;
                    //continue;
                } else {
                    $mFields[] = $field;
                }
            }
            if ($pFields || $activeOnly) {
                if ('account' == $type) {
                    $primaryKey = 'id';
                } else {
                    $primaryKey = 'uid';
                }
                $pFields[] = $primaryKey;
                $model = Pi::model($type, 'user');
                $where = array($primaryKey => $uids);
                if ($activeOnly && 'account' == $type) {
                    $where['active'] = 1;
                }
                $select = $model->select()->where($where)->columns($pFields);
                $rowset = $model->selectWith($select);
                $uids = array();
                foreach ($rowset as $row) {
                    $id = (int) $row[$primaryKey];
                    $uids[] = $id;
                    if ($filter) {
                        $user = $row->filter($pFields) ? : $row->toArray();
                    } else {
                        $user = $row->toArray();
                    }
                    if (isset($result[$id])) {
                        $result[$id] = array_merge($result[$id], $user);
                    } else {
                        $result[$id] = $user;
                    }
                }
            }
            if ($uids) {
                foreach ($mFields as $field) {
                    $handler = new $meta[$field]['handler']($field);
                    $data  = $handler->mget($uids, $filter);
                    foreach ($data as $id => $user) {
                        $result[$id][$field] = $user;
                    }

                }
            }
        } elseif ('compound' == $type) {
            foreach ($fields as $field) {
                $data = Pi::api('compound', 'user')->mget($uids, $field, $filter);
                foreach ($data as $id => $user) {
                    $result[$id][$field] = $user;
                }
            }
        }

        // Canonize uid
        if (is_scalar($uid)) {
            if (isset($result[$uid])) {
                $result = $result[$uid];
            } else {
                $result = array();
            }
        } else {
            $sorted = array();
            foreach ($uid as $id) {
                $sorted[$id] = isset($result[$id]) ? $result[$id] : null;
            }
            $result = $sorted;
        }

        return $result;
    }

    /**
     * Set field for a account/profile type
     *
     * @param int $uid
     * @param string $type
     * @param string $field
     * @param mixed $value
     *
     * @return bool
     */
    protected function setTypeField($uid, $type, $field, $value)
    {
        if (!$uid) {
            return false;
        }

        //$result = false;
        if ('account' == $type || 'profile' == $type) {
            $meta = $this->getMeta($type);
            if (isset($meta[$field])) {
                if (!empty($meta[$field]['handler'])) {
                    $handler = new $meta[$field]['handler']($field);
                    $result = $handler->update($uid, $value);
                } else {
                    if ('account' == $type) {
                        $primaryKey = 'id';
                    } else {
                        $primaryKey = 'uid';
                    }
                    $row = Pi::model($type, 'user')->find($uid, $primaryKey);
                    $row[$field] = $value;
                    try {
                        $row->save();
                        $result = true;
                    } catch (\Exception $e) {
                        $result = false;
                    }
                }
            }

        } elseif ('profile' == $type) {
            $model = Pi::model($type, 'user');
            $row = $model->select(array(
                'uid'   => $uid,
                'field' => $field
            ))->current();
            $row['value'] = $value;
            try {
                $row->save();
                $result = true;
            } catch (\Exception $e) {
                $result = false;
            }
        } elseif ('compound' == $type) {
            $meta = $this->getMeta($type);
            if (isset($meta[$field])) {
                if (!empty($meta[$field]['handler'])) {
                    $handler = new $meta[$field]['handler']($field);
                    $result = $handler->update($uid, $value);
                } else {
                    $result = $this->setCompoundField($uid, $field, $value);
                }
            }
            /*
        } elseif ('custom' == $type) {
            $meta = $this->getMeta('custom');
            if (isset($meta[$field]) && !empty($meta[$field]['handler'])) {
                $handler = new $meta[$field]['handler']($field);
                $result = $handler->update($value);
            }
            */
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * Set user's compound field data
     *
     * @param int       $uid
     * @param string    $compound
     * @param array     $data
     *
     * @return bool
     */
    protected function setCompoundField($uid, $compound, array $data)
    {
        $model = Pi::model('compound', 'user');
        try {
            $model->delete(array(
                'uid'       => $uid,
                'compound'  => $compound,
            ));
        } catch (\Exception $e) {
            return false;
        }

        $compoundSet = $this->canonizeCompound($uid, $compound, $data);
        foreach ($compoundSet as $field) {
            $row = $model->createRow($field);
            try {
                $row->save();
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }
}
