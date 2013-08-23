<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Api;

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
    protected $module = 'user';

    /**
     * Get field names of specific type and action
     *
     * - Available types: `account`, `profile`, `custom`, `compound`
     * - Available actions: `display`, `edit`, `search`
     *
     * @param string $type
     * @param string $action
     * @return string[]
     * @api
     */
    public function getMeta($type = '', $action = '')
    {
        $fields = Pi::registry('profile', 'user')->read($type, $action);

        return array_keys($fields);
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
        if (!isset($data['account']['active'])) {
            $data['account']['active'] = 1;
        }

        $modelAccount = Pi::model('account', 'user');
        // Only account fields
        $accountOnly = count($data) == 1 ? true : false;
        if ($accountOnly) {
            $select = $modelAccount->select();
            $dataAccount = $data['account'];
            $select->columns(array('id'));
            $select->where($dataAccount);
            if ($order) {
                $select->order($order);
            }
        // Multi-types
        } else {
            $select = Pi::db()->select();
            $select->from(array('account' => $modelAccount->getTable()));
            $select->columns(array('id'));

            $canonizeColumn = function ($data, $type) {
                $result = array();
                foreach ($data as $col => $val) {
                    $result[$type . '.' . $col] = $val;
                }
                return $result;
            };
            $where = $canonizeColumn($data['account'], 'account');
            unset($data['account']);

            foreach ($data as $type => $list) {
                $where = array_merge($where, $canonizeColumn($list, $type));
                $model = Pi::model($type, 'user');
                $select->join(
                    array($type => $model->getTable()),
                    $type . '.uid=account.id',
                    array()
                );
            }
            $select->where($where);
            if ($order) {
                if (is_array($order)) {
                    $fields = Pi::registry('profile', 'user')->read();
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

        if ($limit) {
            $select->limit($limit);
        }
        if ($offset) {
            $select->offset($offset);
        }
        if ($accountOnly) {
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
        if (!isset($data['account']['active'])) {
            $data['account']['active'] = 1;
        }

        $modelAccount = Pi::model('account', 'user');
        // Only account fields
        $accountOnly = count($data) == 1 ? true : false;
        if ($accountOnly) {
            $dataAccount = $data['account'];
            $select = $modelAccount->select()->where($dataAccount)
                ->columns(array(
                    'count' => Pi::db()->expression('COUNT(*)')
                ));
            $row = $modelAccount->selectWith($select)->current();
            $count = (int) $row['count'];
        } else {
            $select = Pi::db()->select();
            $select->from(array('account' => $modelAccount->getTable()));
            $select->columns(array(
                'count' => Pi::db()->expression('COUNT(account.id)'),
            ));
            $canonizeColumn = function ($data, $type) {
                $result = array();
                foreach ($data as $col => $val) {
                    $result[$type . '.' . $col] = $val;
                }
                return $result;
            };
            $where = $canonizeColumn($data['account'], 'account');
            unset($data['account']);

            foreach ($data as $type => $list) {
                $where = array_merge($where, $canonizeColumn($list, $type));
                $model = Pi::model($type, 'user');
                $select->join(
                    array($type => $model->getTable()),
                    $type . '.uid=account.id',
                    array()
                );
            }
            $select->where($where);
            $row = Pi::db()->query($select)->current();
            $count = (int) $row['count'];
        }

        return $count;
    }

    /**
     * Add a user with full set of data
     *
     * Full procedure:
     *
     * - Add account data and get uid
     * - Add profile data
     * - Add custom data, multiple
     * - Add compound data, multiple, if any
     *
     * @param   array       $data
     *
     * @return  array   uid and status of profile/custom/compound
     * @api
     */
    public function addUser($data)
    {
        $result = array();
        $uid = $this->addAccount($data);
        if ($uid) {
            $status = $this->addProfile($uid, $data);
            if (!$status) {
                $result['profile'] = false;
            }
            $status = $this->addCustom($uid, $data);
            if (!$status) {
                $result['custom'] = false;
            }
            $status = $this->addCompound($uid, $data);
            if (!$status) {
                $result['compound'] = false;
            }
        }

        return array($uid, $result);
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
     * @return  bool[]
     * @api
     */
    public function updateUser($uid, array $data)
    {
        $result = array();
        $status = $this->updateAccount($uid, $data);
        if (!$status) {
            $result['account'] = false;
        }
        $status = $this->updateProfile($uid, $data);
        if (!$status) {
            $result['profile'] = false;
        }
        $status = $this->updateCustom($uid, $data);
        if (!$status) {
            $result['custom'] = false;
        }
        $status = $this->updateCompound($uid, $data);
        if (!$status) {
            $result['compound'] = false;
        }

        return $result;
    }

    /**
     * Delete a user
     *
     * @param   int         $uid
     * @return  bool[]
     * @api
     */
    public function deleteUser($uid)
    {
        $status = $this->deleteAccount($uid);
        if (!$status) {
            $result['account'] = false;
        }
        $status = $this->deleteProfile($uid);
        if (!$status) {
            $result['profile'] = false;
        }
        $status = $this->deleteCustom($uid);
        if (!$status) {
            $result['custom'] = false;
        }
        $status = $this->deleteCompound($uid);
        if (!$status) {
            $result['compound'] = false;
        }

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
        $keys   = (array) $field;
        $uids   = (array) $uid;

        $meta   = $this->canonizeMeta($keys);
        foreach ($meta as $type => $fields) {
            $fields = $this->getFields($uids, $type, $fields, $filter);
            foreach ($fields as $id => $data) {
                if (isset($result[$id])) {
                    $result[$id] += $data;
                } else {
                    $result[$id] = $data;
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
        $fieldMeta = Pi::registry('profile', 'user')->read();
        if (isset($fieldMeta[$field])) {
            $type = $fieldMeta[$field];
            $result = $this->setTypeField($uid, $type, $field, $value);
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * Increment/decrement a user field
     *
     * Positive to increment or negative to decrement
     *
     * @param int       $uid
     * @param string    $field
     * @param int       $value
     *
     * @return bool
     * @api
     */
    public function increment($uid, $field, $value)
    {
        $fieldMeta = Pi::registry('profile', 'user')->read();
        if (!isset($fieldMeta[$field])) {
            return false;
        }

        $type = $fieldMeta[$field];
        $model = Pi::model($type, 'user');
        if ($value > 0) {
            $string = '+' . $value;
        } else {
            $string = '-' . abs($value);
        }
        if ('account' == $type || 'profile' == $type) {
            $sql = 'UPDATE ' . $model->getTable()
                . ' SET `' . $field . '`=`' . $field . '`' . $string
                . ' WHERE `uid`=' . $uid;
            Pi::db()->getAdapter()->query($sql);
        } elseif ('custom' == $type) {
            $sql = 'UPDATE ' . $model->getTable()
                . ' SET `value`=`value`' . $string
                . ' WHERE `uid`=' . $uid
                . ' AND `field`=' . $field;
            Pi::db()->getAdapter()->query($sql);
        }

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
        $fieldMeta = Pi::registry('profile', 'user')->read();
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
        $fields = Pi::registry('compound', 'user')->read($compound);
        $meta = array_keys($fields);
        $canonizeSet = function ($data, $set) use ($uid, $compound, $meta) {
            $result = array();
            foreach (array_keys($data) as $key) {
                if (!in_array($key, $meta)) {
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

        $fields = Pi::registry('profile', 'user')->read($type);
        foreach ($rawData as $key => $value) {
            if (isset($fields[$key])) {
                if ($type) {
                    $result[$key] = $value;
                } else {
                    $result[$fields[$key]['type']][$key] = $value;
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
        $type = 'account';
        $data = $this->canonizeUser($data, $type);
        if (!isset($data['time_created'])) {
            $data['time_created'] = time();
        }
        $row = Pi::model($type, 'user')->createRow($data);
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
        $type = 'account';
        $data = $this->canonizeUser($data, $type);
        $row = Pi::model($type, 'user')->find($uid);
        if ($row) {
            $row->assign($data);
            //if (isset($data['credential']))
            $row->prepare()->save();
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
        $model = Pi::model('account', 'user');
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
        $model = Pi::model('account', 'user');
        $row = $model->find($uid);
        if (!$row || (int) $row['time_activated'] > 0 || (int) $row['time_deleted'] > 0) {
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
        $model = Pi::model('account', 'user');
        $row = $model->find($uid);
        if (!$row || (int) $row['time_activated'] < 1 || (int) $row['time_deleted'] > 0) {
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
     * Add user profile data
     *
     * @param int   $uid
     * @param array $data
     *
     * @return bool
     */
    public function addProfile($uid, array $data)
    {
        $type = 'profile';
        $data = $this->canonizeUser($data, $type);
        $data['uid'] = $uid;
        $row = Pi::model($type, 'user')->createRow($data);
        $row->save();

        return true;
    }

    /**
     * Update user basic profile data
     *
     * @param int   $uid
     * @param array $data
     *
     * @return bool
     */
    public function updateProfile($uid, array $data)
    {
        $type = 'profile';
        $data = $this->canonizeUser($data, $type);
        $status = Pi::model($type, 'user')->update($data, array('uid' => $uid));

        return $status;
    }

    /**
     * Delete user profile data
     *
     * @param int $uid
     *
     * @return bool
     */
    public function deleteProfile($uid)
    {
        $type = 'profile';
        $status = Pi::model($type, 'user')->delete(array('uid' => $uid));

        return $status;
    }

    /**
     * Add user custom profile
     *
     * @param int   $uid
     * @param array $data
     *
     * @return bool
     */
    public function addCustom($uid, array $data)
    {
        $type = 'custom';
        $data = $this->canonizeUser($data, $type);
        $model = Pi::model($type, 'user');
        foreach ($data as $field => $value) {
            $row = $model->createRow(array(
                'field' => $field,
                'value' => $value,
                'uid'   => $uid,
            ));
            $row->save();
        }

        return true;
    }

    /**
     * Update custom profile fields
     *
     * @param int   $uid
     * @param array $data
     *
     * @return bool
     */
    public function updateCustom($uid, array $data)
    {
        $type = 'custom';
        $data = $this->canonizeUser($data, $type);
        $model = Pi::model($type, 'user');
        foreach ($data as $field => $value) {
            $row = $model->select(array(
                'uid'   => $uid,
                'field' => $field,
            ))->current();
            $row->assign(array(
                'value' => $value,
            ));
            $row->save();
        }

        return true;
    }

    /**
     * Delete custom fields of a user
     *
     * @param $uid
     *
     * @return bool
     */
    public function deleteCustom($uid)
    {
        $type = 'custom';
        $status = Pi::model($type, 'user')->delete(array('uid' => $uid));

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
        $type = 'compound';
        $data = $this->canonizeUser($data, $type);
        $model = Pi::model($type, 'user');
        foreach ($data as $key => $value) {
            $compoundSet = $this->canonizeCompound($uid, $key, $value);
            foreach ($compoundSet as $field) {
                $row = $model->createRow($field);
                $row->save();
            }
        }

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
        $this->deleteCompound($uid);
        $this->addCompound($uid, $data);

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
        $type = 'compound';
        $status = Pi::model($type, 'user')->delete(array('uid' => $uid));

        return $status;
    }

    /**
     * Get a type of field value(s) of a list of user
     *
     * @param int[]|int $uid
     * @param string    $type
     * @param string[]  $fields
     * @param bool      $filter     To filter for display
     * @return array
     * @api
     */
    public function getFields($uid, $type, $fields = array(), $filter = true)
    {
        $result = array();
        $uids = (array) $uid;
        if (!$fields) {
            $fields = $this->getMeta($type);
        } else {
            $fields = array_unique($fields);
        }

        if ('account' == $type || 'profile' == $type) {
            $primaryKey = 'account' == $type ? 'id' : 'uid';
            $fields[] = $primaryKey;
            $model = Pi::model($type, 'user');
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
        } elseif ('custom' == $type) {
            $model = Pi::model($type, 'user');
            $where = array(
                'uid'   => $uids,
                'field' => $fields,
            );
            $columns = array('uid', 'field', 'value');
            $select = $model->select()->where($where)->columns($columns);
            $rowset = $model->selectWith($select);
            foreach ($rowset as $row) {
                if ($filter) {
                    $value = $row->filter();
                } else {
                    $value = $row['value'];
                }
                $result[(int) $row['uid']][$row['field']] = $value;
            }
        } elseif ('compound' == $type) {
            $model = Pi::model($type, 'user');
            $where = array(
                'uid'       => $uids,
                'compound'  => $fields,
            );
            $rowset = $model->select($where);
            foreach ($rowset as $row) {
                if ($filter) {
                    $value = $row->filter();
                } else {
                    $value = $row['value'];
                }
                $result[(int) $row['uid']][$row['compound']][$row['set']][$row['field']]
                    = $value;
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

    /**
     * Set field for a account/profile/custom type
     *
     * @param int $uid
     * @param string $type
     * @param string $field
     * @param mixed $value
     *
     * @return bool
     */
    public function setTypeField($uid, $type, $field, $value)
    {
        if ('account' == $type || 'profile' == $type) {
            $row = Pi::model($type, 'user')->find($uid);
            $row[$field] = $value;
            $row->save();
        } elseif ('custom' == $type) {
            $model = Pi::model($type, 'user');
            $row = $model->select(array(
                'uid'   => $uid,
                'field' => $field
            ))->current();
            $row['value'] = $value;
            $row->save();
        }

        return true;
    }
}
