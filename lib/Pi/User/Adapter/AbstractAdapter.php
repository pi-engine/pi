<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\User\Adapter;

use Pi;
use Pi\User\BindInterface;
use Pi\User\Model\AbstractModel as UserModel;
use Pi\User\Resource\AbstractResource;

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
 *   - getList($condition, $limit, $offset, $order, $field)
 *   - getCount($condition)
 *   - get($uid, $field, $filter)
 *   - mget($uids, $field, $filter)
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
 *
 * @method activity()
 * @method avatar()
 * @method data()
 * @method message()
 * @method timeline()
 *
 * @property-read AbstractResource $activity
 * @property-read AbstractResource $avatar
 * @property-read AbstractResource $data
 * @property-read AbstractResource $message
 * @property-read AbstractResource $timeline
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractAdapter implements BindInterface
{
    /** @var array Options */
    protected $options = array();

    /**
     * Resource handlers
     *
     * @var array
     */
    protected $resource = array(
        'avatar'    => null,
        'message'   => null,
        'timeline'  => null,
        'relation'  => null,
    );

    /**
     * Bound user account
     * @var UserModel
     */
    protected $model;

    /** @var int Root user id */
    protected $rootUid = 1;

    /**
     * Constructor
     *
     * @param array $options
     *
     * @return \Pi\User\Adapter\AbstractAdapter
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
     * Get an option
     *
     * @return mixed|null
     */
    public function getOption()
    {
        $args = func_get_args();
        $result = $this->options;
        foreach ($args as $name) {
            if (!is_array($result)) {
                $result = null;
                break;
            }
            if (isset($result[$name])) {
                $result = $result[$name];
            } else {
                $result = null;
                break;
            }
        }

        return $result;
    }

    /**
     * Get resource handler or result from handler if args specified
     *
     * @param string $name
     * @param array  $args
     *
     * @return AbstractResource|mixed
     */
    public function getResource($name, $args = array())
    {
        if (!isset($this->resource[$name])) {
            $options = array();
            $class = '';
            $resource = $this->getOption('resource', $name);
            if ($resource) {
                if (is_string($resource)) {
                    $class = $resource;
                } else {
                    if (!empty($resource['class'])) {
                        $class = $resource['class'];
                    }
                    if (isset($this->$resource['options'])) {
                        $options = $resource['options'];
                    }
                }
            }
            if (!$class) {
                $class = 'Pi\User\Resource\\' . ucfirst($name);
            }
            $this->resource[$name] = new $class;
            if ($options) {
                $this->resource[$name]->setOptions($options);
            }
        }
        if ($args) {
            $result = call_user_func_array(
                array($this->resource[$name], 'get'),
                $args
            );
        } else {
            $result = $this->resource[$name];
        }

        return $result;
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
        // Bind user model to handlers
        foreach ($this->resource as $key => $handler) {
            if ($handler instanceof BindInterface) {
                $handler->bind($this->model);
            }
        }

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
        switch ($var) {
            // User activity
            case 'activity':
            // User data
            case 'data':
            // User message
            case 'message':
            // User timeline
            case 'timeline':
                $result = $this->getResource($var);
                break;
            // Avatar
            case 'avatar':
                $result = Pi::service('avatar')->setUser($this->getUser());
                break;
            // User profile field
            default:
                if ($this->model && isset($this->model[$var])) {
                    $result = $this->model[$var];
                }
                break;
        }

        return $result;
    }

    /**
     * Method adapter allows a shortcut
     *
     * Call APIs defined in {@link Pi\User\Adapter\AbstractAdapter}
     *
     * @param  string  $method
     * @param  array  $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        $result = null;
        switch ($method) {
            // User activity
            case 'activity':
            // User data
            case 'data':
            // User message
            case 'message':
            // User timeline
            case 'timeline':
                $result = $this->getResource($method, $args);
                break;
            // Avatar
            case 'avatar':
                $result = Pi::service('avatar')->setUser($this->getUser());
                if ($args) {
                    $result = call_user_func_array(array($result,'get'), $args);
                }
                break;
            // User profile adapter methods
            default:
                break;
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
        $uid = $uid ? intval($uid) : (int) $this->__get('id');

        return $uid;
    }

    /**#@+
     * Meta operations
     */
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
    abstract public function getMeta($type = '', $action = '');
    /**#@-*/

    /**#@+
     * User operations
     */

    /**
     * Check if user is root user
     *
     * @param null|int $uid
     *
     * @return bool
     */
    public function isRoot($uid = null)
    {
        if ($this->rootUid) {
            $uid = $this->verifyUid($uid);
            $result = $this->rootUid === $uid ? true : false;
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * Get user data model
     *
     * @param int|string|null   $uid    User id, identity
     * @param string            $field  Field of the identity
     * @return UserModel|null
     * @api
     */
    abstract public function getUser($uid = null, $field = 'id');

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
     * Get users subject to conditions
     *
     * @param array         $condition
     * @param int           $limit
     * @param int           $offset
     * @param string|array  $order
     * @param array         $field
     *
     * @return array
     * @api
     */
    abstract public function getList(
        array $condition    = array(),
        $limit              = 0,
        $offset             = 0,
        $order              = '',
        array $field        = array()
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
     * @param   array   $fields
     * @param   bool    $setRole
     *
     * @return  int|bool
     * @api
     */
    abstract public function addUser($fields, $setRole = true);

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
     * @param bool              $activeOnly
     *
     * @return mixed|mixed[]
     * @api
     */
    abstract public function get(
        $uid,
        $field = array(),
        $filter = false,
        $activeOnly = false
    );

    /**
     * Get field value(s) of users per operation actions
     *
     * Usage:
     *
     * - Get single-field
     *
     * ```
     *  // Raw data
     *  $fields = Pi::api('user', 'user')->get(array(12, 34), 'gender');
     *  // Output:
     *  array(
     *      12  => 'male',
     *      34  => 'unknown',
     *  );
     *
     *  // Filter for display
     *  $fields = Pi::api('user', 'user')->get(array(12, 34), 'gender', true);
     *  // Output:
     *  array(
     *      12  => 'Male',
     *      34  => 'Unknown',
     *  );
     * ```
     *
     * - Get multi-field for display
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
     * @param int[]         $uids
     * @param string|string[]   $field
     * @param bool              $filter
     * @param bool              $activeOnly
     *
     * @return mixed[]
     * @api
     */
    abstract public function mget(
        array $uids,
        $field = array(),
        $filter = false,
        $activeOnly = false
    );

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
     *
     * @return bool
     */
    abstract public function setRole($uid, $role);

    /**
     * Revoke user role(s)
     *
     * @param int           $uid
     * @param string|array  $role
     *
     * @return bool
     */
    abstract public function revokeRole($uid, $role);

    /**
     * Get user role
     *
     * Section: `admin`, `front`
     * If section is specified, returns the role;
     * if not, return associative array of roles.
     *
     * @param int       $uid
     * @param string    $section   Section name: admin, front
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
     * - home: URI to user home (timeline) page
     * - profile: URI to user profile page
     * - (login: URI to user login page)
     * - (logout: URI to user logout page)
     * - register: URI to user register page
     *
     * @param string    $type URL type
     * @param mixed     $options User id for profile or redirect for login
     *
     * @return string
     * @api
     */
    abstract public function getUrl($type, $options = null);

    /**
     * Authenticate user
     *
     * Alias to `Pi::service('authentication')->authenticate()`, discouraged.
     *
     * @param string        $identity
     * @param string        $credential
     *
     * @return Pi\Authentication\Result
     * @deprecated
     * @api
     */
    public function authenticate($identity, $credential)
    {
        trigger_error(__METHOD__ . ' is deprecated.', E_USER_WARNING);

        $result = Pi::service('authentication')->authenticate(
            $identity,
            $credential
        );

        return $result;
    }

    /**
     * Kill a user's session
     *
     * @param int $uid
     *
     * @return bool|null true for success, false for fail, null for no action
     */
    abstract public function killUser($uid);
    /**#@-*/

    /**
     * Get a user model
     *
     * @param int|string|array  $uid
     * @param string            $field
     *
     * @return UserModel
     */
    abstract public function getUserModel($uid, $field = 'id');
}
