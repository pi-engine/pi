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

/**
 * User service abstract class
 *
 * User APIs
 *
 * + Field meta operations
 *   - getMeta($type, $action)
 *
 * + Single user account operations
 *   + Binding
 *   - bind($uid, $field)
 *
 *   + Read
 *   - getUser($uid, $field)
 *
 *   + Add
 *   - addUser($data)
 *
 *   + Update
 *   - updateUser($uid, $data)
 *
 *   + Delete
 *   - deleteUser($uid)
 *
 *   + Activate account
 *   - activateUser($uid)
 *
 *   + Enable/Disable
 *   - enableUser($uid)
 *   - disableUser($uid)
 *
 * + User account/profile field operations
 *   + Read
 *   - getUids($condition, $limit, $offset, $order)
 *   - getCount($condition)
 *   - get($uid, $field, $filter)
 *
 *   + Update
 *   - set($uid, $field, $value)
 *
 * + Utility
 *   + Route for URL assembing
 *   - getRoute()
 *   + Collective URL
 *   - getUrl($type, $uid = null)
 *   + Authentication
 *   - authenticate($identity, $credential)
 *   - killUser($uid)
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractAdapter implements BindInterface
{
    /** @var array Options */
    protected $options = array();

    /**
     * Bound user account
     * @var UserModel
     */
    protected $model;

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        $this->setOptions($options);
    }

    /**
     * Set options
     *
     * @param array $options
     * @return self
     */
    public function setOptions($options = array())
    {
        $this->options = $options;

        return $this;
    }

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
            $result = $this->model[$var];
        }

        return $result;
    }

    /**
     * Verify 'uid' parameter
     *
     * @param $uid
     * @return int
     */
    protected function verifyUid($uid)
    {
        $uid = $uid ? intval($uid) : $this->__get($uid);

        return $uid;
    }

    /**#@+
     * Meta operations
     */
    /**
     * Get fields specs of specific type and action
     *
     * - Available types: `account`, `profile`, `custom`
     * - Available actions: `display`, `edit`, `search`
     *
     * @param string $type
     * @param string $action
     * @return array
     * @api
     */
    abstract public function getMeta($type = '', $action = '');
    /**#@-*/

    /**#@+
     * User operations
     */

    /**
     * Get user data model
     *
     * @param int|string|null   $uid    User id, identity
     * @param string            $field  Field of the identity
     * @return UserModel
     * @api
     */
    abstract public function getUser($uid, $field = 'id');

    /**
     * Get user IDs subject to conditions
     *
     * @param array         $condition
     * @param int           $limit
     * @param int           $offset
     * @param string        $order
     * @return int[]
     * @api
     */
    abstract public function getUids(
        $condition  = array(),
        $limit      = 0,
        $offset     = 0,
        $order      = ''
    );

    /**
     * Get user count subject to conditions
     *
     * @param array  $condition
     * @return int
     * @api
     */
    abstract public function getCount($condition = array());

    /**
     * Add a user
     *
     * @param   array       $fields
     * @return  int|bool
     * @api
     */
    abstract public function addUser($fields);

    /**
     * Update a user
     *
     * @param   int         $uid
     * @param   array       $fields
     * @return  int|bool
     * @api
     */
    abstract public function updateUser($uid, $fields);

    /**
     * Delete a user
     *
     * @param   int         $uid
     * @return  bool|null   Null for no-action
     * @api
     */
    abstract public function deleteUser($uid);

    /**
     * Activate a user
     *
     * @param   int         $uid
     * @return  bool|null   Null for no-action
     * @api
     */
    abstract public function activateUser($uid);

    /**
     * Enable a user
     *
     * @param   int         $uid
     * @return  bool|null   Null for no-action
     * @api
     */
    abstract public function enableUser($uid);

    /**
     * Disable a user
     *
     * @param   int         $uid
     * @return  bool|null   Null for no-action
     * @api
     */
    abstract public function disableUser($uid);
    /**#@-*/

    /**#@+
     * User account/Profile fields operations
     */
    /**
     * Get field value(s) of user(s) per operation actions
     *
     * Usage:
     *
     * - Get single-user-single-field
     *
     * ```
     *  // Raw data
     *  $fields = Pi::api('user', 'user')->get(12, 'gender');
     *  // Output: 'male' (or 'female', 'unknown')
     *
     *  // Filter for display
     *  $fields = Pi::api('user', 'user')->get(123, 'gender', true);
     *  // Output: 'Male' (or 'Female', 'Unknown')
     * ```
     *
     * - Get multi-user-multi-field for display
     *
     * ```
     *  // Filter for display
     *  $fields = Pi::api('user', 'user')->get(
     *      array(12, 34, 56),
     *      array('name', 'gender'),
     *      true
     *  );
     *  // Output:
     *  array(
     *      12  => array(
     *          'name'      => 'John',
     *          'gender'    => 'Male',
     *      ),
     *      34  => array(
     *          'name'      => 'Joe',
     *          'gender'    => 'Unknown',
     *      ),
     *      56  => array(
     *          'name'      => 'Rose',
     *          'gender'    => 'Female',
     *      ),
     *  );
     * ```
     *
     * @param int|int[]         $uid
     * @param string|string[]   $field
     * @param bool              $filter
     * @return mixed|mixed[]
     * @api
     */
    abstract public function get($uid, $field, $filter = false);

    /**
     * Set value of a user field
     *
     * @param int       $uid
     * @param string    $field
     * @param mixed     $value
     * @return bool
     * @api
     */
    abstract public function set($uid, $field, $value);
    /**#@-*/

    /**
     * Set user role(s)
     *
     * @param int           $uid
     * @param string|array  $role
     * @param string        $section
     *
     * @return bool
     */
    abstract public function setRole($uid, $role, $section = '');

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
    abstract public function getRole($uid, $section = '');

    /**#@+
     * Utility APIs
     */
    /**
     * Get route for URL assembling
     *
     * @return string
     */
    abstract public function getRoute();

    /**
     * Get user URL
     *
     * - account: URI to user account page
     * - profile: URI to user profile page
     * - login: URI to user login page
     * - logout: URI to user logout page
     * - register (signup): URI to user register/signup page
     *
     * @param string            $type URL type
     * @param int|string|null   $var User id for profile or redirect for login
     * @return string
     * @api
     */
    abstract public function getUrl($type, $var = null);

    /**
     * Authenticate user
     *
     * Authenticate a user and display corresponding message
     *
     * ```
     *  $result = Pi::service('user')->authenticate(<identity>, <credential>);
     *  if ($result->isValid()) {
     *      echo 'User is logged on.';
     *      Pi::service('user')->setPersist($result->getData();
     *  } else {
     *      echo implode('<br>', $result->getMessages());
     *  }
     * ```
     *
     * @param string        $identity
     * @param string        $credential
     * @return Pi\Authentication\Result
     * @api
     */
    abstract public function authenticate($identity, $credential);

    /**
     * Kill a user's session
     *
     * @param int $uid
     *
     * @return bool|null true for success, false for fail, null for no action
     */
    abstract public function killUser($uid);
    /**#@-*/
}
