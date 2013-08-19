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
use Zend\Db\Sql\Predicate\PredicateInterface;

/**
 * User service abstract class
 *
 * User APIs
 *
 * + Field meta operations
 *   - getMeta($type, $action)
 *
 * + User operations
 *   + Binding
 *   - bind($uid[, $field])
 *
 *   + Read
 *   - getUser(<uid>|array(<field>))
 *   - getUids($condition, $limit, $offset, $order)
 *   - getCount($condition)
 *
 *   + Add
 *   - addUser($data)
 *
 *   + Update
 *   - updateUser($data, $uid)
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
 *   - get($key, $uid)
 *   - getList($key, $uids)
 *
 *   + Update
 *   - set($key, $value, $uid)
 *   - increment($key, $value, $uid)
 *
 * + Utility
 *   + Collective URL
 *   - getUrl($type[, $uid])
 *   + Authentication
 *   - authenticate($identity, $credential)
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
            $result = $this->model->$var;
        }

        return $result;
    }

    /**
     * Verify uid paramter
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
     * Get field names of specific type and action
     *
     * - Available types: `account`, `profile`, `custom`
     * - Available actions: `display`, `edit`, `search`
     *
     * @param string $type
     * @param string $action
     * @return string[]
     * @api
     */
    abstract public function getMeta($type = '', $action = '');
    /**#@-*/

    /**#@+
     * User operations
     */
    /**
     * Get user data object
     *
     * Use different type of identity: id, identity, email, etc.
     *
     * @param int|string|null   $uid         User id, identity
     * @param string            $field      Field of the identity
     * @return UserModel
     * @api
     */
    abstract public function getUser($uid = null, $field = 'id');

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
    abstract public function getUids(
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
     * @return  int|bool
     * @api
     */
    abstract public function addUser($data);

    /**
     * Update a user
     *
     * @param   array       $data
     * @param   int         $uid
     * @return  int|bool
     * @api
     */
    abstract public function updateUser($data, $uid = null);

    /**
     * Delete a user
     *
     * @param   int         $uid
     * @return  bool
     * @api
     */
    abstract public function deleteUser($uid);

    /**
     * Activate a user
     *
     * @param   int         $uid
     * @return  bool
     * @api
     */
    abstract public function activateUser($uid);

    /**
     * Enable a user
     *
     * @param   int         $uid
     * @return  bool
     * @api
     */
    abstract public function enableUser($uid);

    /**
     * Disable a user
     *
     * @param   int         $uid
     * @return  bool
     * @api
     */
    abstract public function disableUser($uid);
    /**#@-*/

    /**#@+
     * User account/Profile fields operations
     */
    /**
     * Get field value(s) of a user field(s)
     *
     * @param string|array      $key
     * @param string|int|null   $uid
     * @return mixed|mixed[]
     * @api
     */
    abstract public function get($key, $uid = null);

    /**
     * Get field value(s) of a list of user
     *
     * @param string|array      $key
     * @param array             $uids
     * @return array
     * @api
     */
    abstract public function getList($key, $uids);

    /**
     * Set value of a user field
     *
     * @param string            $key
     * @param mixed             $value
     * @param string|int|null   $uid
     * @return bool
     * @api
     */
    abstract public function set($key, $value, $uid = null);

    /**
     * Incremetn/decrement a user field
     *
     * @param string            $key
     * @param int               $value
     *      Positive to increment or negative to decrement
     * @param string|int|null   $uid
     * @return bool
     * @api
     */
    abstract public function increment($key, $value, $uid = null);
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
     * @param string        $type
     *      Type of URLs: profile, login, logout, register, auth
     * @param int|null      $uid
     * @return string
     * @api
     */
    abstract public function getUrl($type, $uid = null);

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
        trigger_error(
            sprintf(__CLASS__ . '::%s is not defined yet.', $method),
            E_USER_NOTICE
        );

        return 'Not defined';
    }
}
