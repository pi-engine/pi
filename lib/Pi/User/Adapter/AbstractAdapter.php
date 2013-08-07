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
 *   - getMeta([$type])
 *
 * + User operations
 *   + Binding
 *   - bind($id[, $field])
 *
 *   + Read
 *   - getUser([$id])
 *   - getUserList($ids)
 *   - getIds($condition[, $limit[, $offset[, $order]]])
 *   - getCount([$condition])
 *
 *   + Add
 *   - addUser($data)
 *
 *   + Update
 *   - updateUser($data[, $id])
 *
 *   + Delete
 *   - deleteUser($id)
 *
 *   + Activate
 *   - activateUser($id)
 *   - deactivateUser($id)
 *
 * + User account/profile field operations
 *   + Read
 *   - get($key[, $id])
 *   - getList($key, $ids)
 *
 *   + Update
 *   - set($key, $value[, $id])
 *   - increment($key, $value[, $id])
 *   - setPassword($value[, $id])
 *
 * + Utility
 *   + Collective URL
 *   - getUrl($type[, $id])
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

    /**#@+
     * Meta operations
     */
    /**
     * Get meta with type: account, profile, extra
     *
     * @param string $type
     * @return array
     * @api
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
     * @param string            $field      Field of the identity:
     *      id, identity, email, etc.
     * @return UserModel
     * @api
     */
    abstract public function getUser($id = null, $field = 'id');

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
    abstract public function getIds($condition = array(),
        $limit = 0, $offset = 0, $order = '');

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
    abstract public function addUser($data);

    /**
     * Update a user
     *
     * @param   array       $data
     * @param   int         $id
     * @return  int|false
     * @api
     */
    abstract public function updateUser($data, $id = null);

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
     * @api
     */
    abstract public function get($key, $id = null);

    /**
     * Get field value(s) of a list of user
     *
     * @param string|array      $key
     * @param array             $ids
     * @return array
     * @api
     */
    abstract public function getList($key, $ids);

    /**
     * Set value of a user field
     *
     * @param string            $key
     * @param midex             $value
     * @param string|int|null   $id
     * @return bool
     * @api
     */
    abstract public function set($key, $value, $id = null);

    /**
     * Incremetn/decrement a user field
     *
     * @param string            $key
     * @param int               $value
     *      Positive to increment or negative to decrement
     * @param string|int|null   $id
     * @return bool
     * @api
     */
    abstract public function increment($key, $value, $id = null);

    /**
     * Set a user password
     *
     * @param string            $value
     * @param string|int|null   $id
     * @return bool
     * @api
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
     * @param string        $type
     *      Type of URLs: profile, login, logout, register, auth
     * @param int|null      $id
     * @return string
     * @api
     */
    abstract public function getUrl($type, $id = null);

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
     * @return \Pi\Authentication\Result
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
