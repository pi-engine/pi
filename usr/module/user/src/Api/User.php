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
//use Pi\Db\RowGateway\RowGateway;
//use Zend\Stdlib\ArrayUtils;

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
     * @param array|PredicateInterface  $condition
     * @param int                       $limit
     * @param int                       $offset
     * @param string                    $order
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

        trigger_error(__METHOD__ . ' not implemented yet.');

        return $result;
    }

    /**
     * Get user count subject to conditions
     *
     * @param array|PredicateInterface  $condition
     *
     * @return int
     * @api
     */
    public function getCount($condition = array())
    {
        $count = 0;

        trigger_error(__METHOD__ . ' not implemented yet.');

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
     * @return  int|false
     * @api
     */
    public function addUser($data)
    {
        $uid = $this->addAccount($data);
        $status = $this->addProfile($data, $uid);
        $status = $this->addCustom($data, $uid);
        $status = $this->addCompound($data, $uid);

        return $uid;
    }

    /**
     * Update a user
     *
     * @param   array       $data
     * @param   int         $uid
     *
     * @return  bool
     * @api
     */
    public function updateUser($data, $uid)
    {
        $status = $this->updateAccount($data, $uid);
        $status = $this->updateProfile($data, $uid);
        $status = $this->updateCustom($data, $uid);
        $status = $this->updateCompound($data, $uid);

        return $status;
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
        $status = $this->deleteProfile($uid);
        $status = $this->deleteCustom($uid);
        $status = $this->deleteCompound($uid);

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
     * @param string|array      $key
     * @param string|int|null   $uid
     * @return mixed|mixed[]
     * @api
     */
    public function get($key, $uid)
    {
        $keys   = (array) $key;
        $meta   = $this->canonizeMeta($keys);
        $result = array();
        foreach ($meta as $type => $fields) {
            $fields = $this->getFields($uid, $type, $fields);
            $result += $fields;
        }
        if (is_string($key)) {
            $result = isset($result[$key]) ? $result[$key] : null;
        }

        return $result;
    }

    /**
     * Get field value(s) of a list of user
     *
     * @param string|array      $key
     * @param array             $uids
     * @return array
     * @api
     */
    public function getList($key, array $uids)
    {
        $keys   = (array) $key;
        $meta   = $this->canonizeMeta($keys);
        $result = array();
        foreach ($meta as $type => $fields) {
            $fields = $this->getFields($uids, $type, $fields);
            $result = array_merge($result, $fields);
        }
        if (is_string($key)) {
            $result = isset($result[$key]) ? $result[$key] : null;
        }

        return $result;
    }

    /**
     * Set value of a user field
     *
     * @param string    $key
     * @param mixed     $value
     * @param int       $uid
     * @return bool
     * @api
     */
    public function set($key, $value, $uid)
    {
        $fieldMeta = Pi::registry('profile', 'user')->read();
        if (isset($fieldMeta[$key])) {
            $type = $fieldMeta[$key];
            $result = $this->setField($type, $key, $value, $uid);
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
     * @param string    $key
     * @param int       $value
     * @param int       $uid
     *
     * @return bool
     * @api
     */
    public function increment($key, $value, $uid)
    {
        $fieldMeta = Pi::registry('profile', 'user')->read();
        if (isset($fieldMeta[$key])) {
            $type = $fieldMeta[$key];
            $result = $this->incrementField($type, $key, $value, $uid);
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * Canonize profile field list to group by types
     *
     * @param array $fields
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
     *          'çompound'  => <compound>,
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
     *          'çompound'  => <compound>,
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
        $fields = Pi::registry('profile', 'user')->read($compound);
        $meta = array_keys($fields);
        $canonizeSet = function ($data, $set) use ($uid, $compound, $meta) {
            $result = array();
            foreach (array_keys($data) as $key) {
                if (!in_array($key, $meta)) {
                    unset($data[$key]);
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
        $keys = array_keys($rawData);
        if (is_int($keys[0])) {
            $set = 0;
            foreach ($rawData as $key => $data) {
                $result += $canonizeSet($data, $set);
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
        $type = 'account';
        $data = $this->canonizeUser($data, $type);
        if (!isset($data['time_created'])) {
            $data['time_created'] = time();
        }
        $row = Pi::model($type, 'user')->createRow($data);
        $row->save();

        return $row->id;
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
        if ($row->time_deleted > 0) {
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
        if ($row->time_activated > 0 || $row->time_deleted > 0) {
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
        if ($row->time_activated < 0 || $row->time_deleted > 0) {
            return false;
        }
        if (($flag && $row->time_disabled < 0)
            || (!$flag && $row->time_disabled > 0)
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
     * @param array $data
     * @param int   $uid
     *
     * @return bool
     */
    public function addProfile(array $data, $uid)
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
     * @param array $data
     * @param int $uid
     *
     * @return bool
     */
    public function updateProfile(array $data, $uid)
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
     * @param array $data
     * @param int   $uid
     *
     * @return bool
     */
    public function addCustom(array $data, $uid)
    {
        $type = 'custom';
        $data = $this->canonizeUser($data, $type);
        $model = Pi::model($type, 'user');
        foreach ($data as $key => $value) {
            $row = $model->createRow(array(
                'field' => $key,
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
     * @param array $data
     * @param int   $uid
     *
     * @return bool
     */
    public function updateCustom(array $data, $uid)
    {
        $type = 'custom';
        $data = $this->canonizeUser($data, $type);
        $model = Pi::model($type, 'user');
        foreach ($data as $key => $value) {
            $row = $model->select(array(
                'uid'   => $uid,
                'field' => $key,
            ))->current();
            $row = assign(array(
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
     * @param array $data
     * @param int   $uid
     *
     * @return bool
     */
    public function addCompound(array $data, $uid)
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
     * @param array $data
     * @param int   $uid
     *
     * @return bool
     */
    public function updateCompound(array $data, $uid)
    {
        $type = 'compound';
        $this->deleteCompound($uid);
        $this->addCompound($data, $uid);

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
     * @param array     $uids
     * @param string    $type
     * @param array     $fields
     * @return array
     * @api
     */
    public function getFields(array $uids, $type, $fields = array())
    {
        $result = array();
        if ($fields) {
            $fields = $this->canonizeMeta($fields, $type);
        } else {
            $fields = Pi::registry('profile', 'user')->read($type);
        }

        if ('account' == $type || 'profile' == $type) {
            $primaryKey = 'account' == $type ? 'id' : 'uid';
            $fields[] = $primaryKey;
            $model = Pi::model($type, 'user');
            $select = $model->select()->where(array('uid' => $uids))
                ->columns($fields);
            $rowset = $model->selectWith($select);
            foreach ($rowset as $row) {
                $result[$row->{$primaryKey}] = $row->toArray();
            }
        } elseif ('custom' == $type) {
            $model = Pi::model($type, 'user');
            $where = array(
                'uid'   => $type,
                'field' => $fields,
            );
            $columns = array('uid', 'field', 'value');
            $select = $model->select()->where($where)->columns($columns);
            $rowset = $model->selectWith($select);
            foreach ($rowset as $row) {
                $result[$row->uid][$row->field] = $row->value;
            }
        } elseif ('compound' == $type) {
            $model = Pi::model($type, 'user');
            $where = array(
                'uid'       => $type,
                'compound'  => $fields,
            );
            $rowset = $model->select($where);
            foreach ($rowset as $row) {
                $result[$row->uid][$row->compound][$row->set][$row->field]
                    = $row->value;
            }
        }

        return $result;
    }
}
