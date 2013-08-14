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

/**
 * User account manipulation APIs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class User extends AbstractApi
{
    /** @var string Module name */
    protected $module = 'user';

    public function canonizeUser($rawdata)
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
                $result[$type][$key] = $rawData[$key];
            }
        }
        foreach ($result as $type => &$data) {
            $fields = Pi::service('user')->getMeta($type);
            foreach ($rawData as $key => $value) {
                if (in_array($key, $fields)) {
                    $data[$type][$key] = $rawData[$key];
                }
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
