<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\User\Adapter;

use Pi;
use Pi\User\BindInterface;
use Pi\User\Model\AbstractModel as UserModel;
use Pi\User\Avatar\Factory as UserAvatar;
use Zend\Db\Sql\Predicate\PredicateInterface;

/**
 * User service abstract class
 *
 * User APIs
 *
 * + Meta operations
 *   - getMeta([$type])                                     // Get meta list of user, type: account, profile, extra - extra profile non-structured
 *
 * + User operations
 *   + Bind
 *   - bind($id[, $field])                                  // Bind current user
 *
 *   + Read
 *   - getUser([$id])                                       // Get current user or specified user
 *   - getUserList($ids)                                    // List of users by ID list
 *   - getIds($condition[, $limit[, $offset[, $order]]])    // ID list subject to $condition
 *   - getCount([$condition])                               // User count subject to $condition
 *
 *   + Add
 *   - addUser($data)               // Add a new user with account and profile
 *
 *   + Update
 *   - updateUser($data[, $id])     // Update a user for account and profile
 *
 *   + Delete
 *   - deleteUser($id)              // Delete a user
 *
 *   + Activate
 *   - activateUser($id)            // Activate a user
 *   - deactivateUser($id)          // Deactivate a user
 *
 * + User account/profile field operations
 *   + Read
 *   - get($key[, $id])             // Get user field(s)
 *   - getList($key, $ids)          // User field(s) of user list
 *
 *   + Update
 *   - set($key, $value[, $id])         // Update field of user
 *   - increment($key, $value[, $id])   // Increase value of field
 *   - setPassword($value[, $id])       // Update password
 *
 * + Utility
 *   + Collective URL
 *   - getUrl($type[, $id])                                         // URLs with type: profile, login, logout, register, auth (authentication)
 *   + Authentication
 *   - authenticate($identity, $credential[, $identityField])       // Authenticate a user
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractAdapter implements BindInterface
{
    /**
     * Bound user account
     * @var UserModel
     */
    protected $model;

    /**
     * Bind a user to service
     *
     * @param UserModel $user
     * @return AbstractAdapter
     */
    public function bind(UserModel $user = null)
    {
        $this->model = $user;
        return $this;
    }

    /**
     * Get variables of current user
     *
     * @param string $var
     * @return mixed
     */
    public function __get($var)
    {
        $result = null;
        if ($this->model) {
            $result = $this->model->$var;
        }

        return $result;
    }

    /**#@+
     * Meta operations
     */
    /**
     * Get meta with type: account, profile, extra
     *
     * @param string $type
     * @return array
     */
    abstract public function getMeta($type = 'account');
    /**#@-*/

    /**#@+
     * User operations
     */
    /**
     * Get user data object
     *
     * @param int|string|null   $id         User id, identity
     * @param string            $field      Field of the identity: id, identity, email, etc.
     * @return UserModel
     */
    abstract public function getUser($id = null, $field = 'id');

    /**
     * Get user data objects
     *
     * @param int[] $ids User ids
     * @return array
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
     */
    abstract public function getIds($condition = array(), $limit = 0, $offset = 0, $order = '');

    /**
     * Get user count subject to conditions
     *
     * @param array|PredicateInterface  $condition
     * @return int
     */
    abstract public function getCount($condition = array());

    /**
     * Add a user
     *
     * @param   array       $data
     * @return  int|false
     */
    abstract public function addUser($data);

    /**
     * Update a user
     *
     * @param   array       $data
     * @param   int         $id
     * @return  int|false
     */
    abstract public function updateUser($data, $id = null);

    /**
     * Delete a user
     *
     * @param   int         $id
     * @return  bool
     */
    abstract public function deleteUser($id);

    /**
     * Activate a user
     *
     * @param   int         $id
     * @return  bool
     */
    abstract public function activateUser($id);

    /**
     * Deactivate a user
     *
     * @param   int         $id
     * @return  bool
     */
    abstract public function deactivateUser($id);
    /**#@-*/

    /**#@+
     * User account/Profile fields operations
     */
    /**
     * Get field value(s) of a user field(s)
     *
     * @param string|array      $key
     * @param string|int|null   $id
     * @return mixed
     */
    abstract public function get($key, $id = null);

    /**
     * Get field value(s) of a list of user
     *
     * @param string|array      $key
     * @param array             $ids
     * @return array
     */
    abstract public function getList($key, $ids);

    /**
     * Set value of a user field
     *
     * @param string            $key
     * @param midex             $value
     * @param string|int|null   $id
     * @return bool
     */
    abstract public function set($key, $value, $id = null);

    /**
     * Incremetn/decrement a user field
     *
     * @param string            $key
     * @param int               $value  Positive to increment or negative to decrement
     * @param string|int|null   $id
     * @return bool
     */
    abstract public function increment($key, $value, $id = null);

    /**
     * Set a user password
     *
     * @param string            $value
     * @param string|int|null   $id
     * @return bool
     */
    abstract public function setPassword($value, $id = null);
    /**#@-*/

    /**#@+
     * Utility APIs
     */
    /**
     * Get user URL
     *
     * - account: URI to user account page
     * - profile: URI to user profile page
     * - login: URI to user login page
     * - logout: URI to user logout page
     * - register (signup): URI to user register/signup page
     *
     * @param string        $type       Type of URLs: profile, login, logout, register, auth
     * @param int|null      $id
     * @return string
     */
    abstract public function getUrl($type, $id = null);

    /**
     * Authenticate user
     *
     * @param string        $identity
     * @param string        $credential
     * @param string        $field          Identity field: identity, email
     * @return bool
     */
    abstract public function authenticate($identity, $credential, $field = 'identity');
    /**#@-*/

    /**
     * Method handler allows a shortcut
     *
     * @param  string  $method
     * @param  array  $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        trigger_error(sprintf(__CLASS__ . '::%s is not defined yet.', $method), E_USER_NOTICE);
        return 'Not defined';
    }
}