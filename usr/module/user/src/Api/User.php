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
use Pi\Db\RowGateway\RowGateway;
use Zend\Stdlib\ArrayUtils;

/**
 * User account manipulation APIs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class User extends AbstractApi
{
    /** @var string Module name */
    protected $module = 'user';

    public function canonizeCompound($compound, array $rawData)
    {
        $fields = Pi::registry('profile', 'user')->read($compound);
        $meta = array_keys($fields);
        $canonizeSet = function ($data) use ($meta) {
            foreach (array_keys($data) as $key) {
                if (!in_array($key, $meta)) {
                    unset($data[$key]);
                }
            }
            return $data;
        };

        $result = array();
        $keys = array_keys($rawData);
        if (is_int($keys[0])) {
            $set = 0;
            foreach ($rawData as $key => $data) {
                $result[$set++] = $canonizeSet($data);
            }
        } else {
            $result[] = $canonizeSet($rawData);
        }

        return $result;
    }

    public function canonizeUser(array $rawData)
    {
        $result = array(
            'account'   => array(),
            'profile'   => array(),
            'custom'    => array(),
            'compound'  => array(),
        );

        $fields = Pi::registry('profile', 'user')->read();
        foreach ($rawData as $key => $value) {
            if (isset($fields[$key])) {
                $type = $fields[$key]['type'];
                $result[$type][$key] = $value;
                /*
                if ('compound' == $type) {
                    $result[$type][$key] = $this->canonizeCompound(
                        $key,
                        $value
                    );
                } else {
                    $result[$type][$key] = $value;
                }
                */
            }
        }

        return $result;
    }

    /**
     * Get user data object
     *
     * @param int|string|null   $id         User id, identity
     * @param string            $field      Field of the identity:
     *      id, identity, email, etc.
     * @return UserModel
     * @api
     */
    abstract public function getUser($id, $field);

    /**
     * Get user data objects
     *
     * @param int[] $ids User ids
     * @return array
     * @api
     */
    abstract public function getUserList($ids);

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
    abstract public function getIds(
        $condition  = array(),
        $limit      = 0,
        $offset     = 0,
        $order      = ''
    );

    /**
     * Get user count subject to conditions
     *
     * @param array|PredicateInterface  $condition
     * @return int
     * @api
     */
    abstract public function getCount($condition = array());

    /**
     * Add a user
     *
     * @param   array       $data
     * @return  int|false
     * @api
     */
    public function addUser($data)
    {
        $data = $this->canonizeUser($data);
        $uid = $this->addAccount($data['account']);
        $status = $this->addProfile($data['profile'], $uid);
        $status = $this->addCustom($data['custom'], $uid);
        $status = $this->addCompound($data['compound'], $uid);

        return $uid;
    }

    public function addAccount($data)
    {
        if (!isset($data['time_registered'])) {
            $data['time_registered'] = time();
        }
        $row = Pi::model('account', 'user')->createRow($data);
        $row->save();

        return $row->id;
    }

    public function addProfile($data, $uid)
    {
        $data['uid'] = $uid;
        $row = Pi::model('profile', 'user')->createRow($row);
        $row->save();

        return true;
    }

    public function addCustom($data, $uid)
    {
        $model = Pi::model('custom', 'user');
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

    public function addCompound($data, $uid)
    {
        $model = Pi::model('compound', 'user');
        foreach ($data as $key => $value) {
            $compoundSet = $this->canonizeCompound($uid, $key, $value);
            foreach ($compoundSet as $spec) {
                $row = $model->createRow($spec);
                $row->save();
            }
        }
        
        return true;
    }

    /**
     * Update a user
     *
     * @param   array       $data
     * @param   int         $id
     * @return  int|false
     * @api
     */
    abstract public function updateUser($data, $id);

    /**
     * Delete a user
     *
     * @param   int         $id
     * @return  bool
     * @api
     */
    abstract public function deleteUser($id);

    /**
     * Activate a user
     *
     * @param   int         $id
     * @return  bool
     * @api
     */
    abstract public function activateUser($id);

    /**
     * Deactivate a user
     *
     * @param   int         $id
     * @return  bool
     * @api
     */
    abstract public function deactivateUser($id);
}
