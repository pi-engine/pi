<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;
use Pi\User\BindInterface;
use Pi\User\Adapter\AbstractAdapter;
use Pi\User\Adapter\System as DefaultAdapter;
use Pi\User\Model\AbstractModel as UserModel;
use Pi\User\Resource\AbstractResource;

/**
 * User service gateway
 *
 * Serves as a gateway to user account and profile data,
 * to proxy APIs to corresponding adapter,
 * either Pi built-in user or Pi SSO or any third-party user service
 *
 * User APIs
 *
 * Basic APIs defined by `Pi\User\Adapter\AbstractAdapter`
 * called via magic method __call()
 *
 * ----------------------------------------------------------------------------
 *
 * + User operations
 *  - bind($identity[, $type])
 *  - restore()
 *  - destroy()
 *  - hasIdentity()
 *  - getIdentity()
 *
 * + Resource APIs
 *
 * + Activity
 *   - activity($uid, $name, $limit, $offset)
 *   - activity->get($uid, $name, $limit, $offset)
 *
 * + Avatar
 *   - avatar($uid, $size, $attributes, $source)
 *   - avatar->get($uid, $size, $attributes, $source)
 *   - avatar->getList($uids, $size, $attributes, $source)
 *
 * + Data
 *   - data($uid, $name)
 *   - data->get($uid, $name)
 *   - data->set($uid, $name, $value, $module = '', $time = null)
 *   - data->setInt($uid, $name, $value, $module = '', $time = null)
 *   - data->increment($uid, $name, $value, $module = '', $time = null)
 *   - data->delete($uid, $name)
 *
 * + Message
 *   - message->send($ui, $message, $from)
 *   - message->notify($uid, $message, $subject, $tag)
 *   - message->getCount($uid)
 *   - message->getAlert($uid)
 *   - message->dismissAlert($uid)
 *
 * + Timeline
 *   - timeline($uid, $limit, $offset)
 *   - timeline->get($uid, $limit, $offset)
 *   - timeline->getCount($uid)
 *   - timeline->add(array(
 *          'uid'       => <uid>,
 *          'message'   => <message>,
 *          'module'    => <module-name>,
 *          'timeline'  => <timeline-name>,
 *          'link'      => <link-href>,
 *          'time'      => <timestamp>,
 *     ));
 *
 * @method \Pi\User\Adapter\AbstractAdapter::getMeta($type, $action)
 *
 * @method \Pi\User\Adapter\AbstractAdapter::addUser($fields)
 * @method \Pi\User\Adapter\AbstractAdapter::getUser($uid, $fields)
 * @method \Pi\User\Adapter\AbstractAdapter::updateUser($uid, $fields)
 * @method \Pi\User\Adapter\AbstractAdapter::deleteUser($uid)
 * @method \Pi\User\Adapter\AbstractAdapter::activateUser($uid)
 * @method \Pi\User\Adapter\AbstractAdapter::enableUser($uid)
 * @method \Pi\User\Adapter\AbstractAdapter::disableUser($uid)
 *
 * @method \Pi\User\Adapter\AbstractAdapter::getUids($condition = array(), $limit = 0, $offset = 0, $order = '')
 * @method \Pi\User\Adapter\AbstractAdapter::getCount($condition = array())
 * @method \Pi\User\Adapter\AbstractAdapter::get($uid, $field, $action = '')
 * @method \Pi\User\Adapter\AbstractAdapter::set($uid, $field, $value)
 * @method \Pi\User\Adapter\AbstractAdapter::getRoute()
 * @method \Pi\User\Adapter\AbstractAdapter::getUrl($type, $uid = null)
 * @method \Pi\User\Adapter\AbstractAdapter::authenticate($identity, $credential)
 * @method \Pi\User\Adapter\AbstractAdapter::killUser($uid)
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
 * @see Pi\User\Adapter\AbstractAdapter for detailed user specific APIs
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class User extends AbstractService
{
    /** {@inheritDoc} */
    protected $fileIdentifier = 'user';

    /**
     * Bound user data object
     *
     * @var UserModel
     */
    protected $model;

    /**
     * User data object of current session
     *
     * @var UserModel|null|bool
     */
    protected $modelSession;

    /**
     * Service handler adapter
     *
     * @var AbstractAdapter
     */
    protected $adapter;

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
     * Set service adapter
     *
     * @param AbstractAdapter $adapter
     * @return self
     */
    public function setAdapter(AbstractAdapter $adapter)
    {
        $this->adapter = $adapter;
        $this->adapter->bind($this->bind());

        return $this;
    }

    /**
     * Get service adapter
     *
     * Instantiate local adapter if not available
     *
     * @return AbstractAdapter
     */
    public function getAdapter()
    {
        if (!$this->adapter instanceof AbstractAdapter) {
            $options = isset($this->options['options'])
                ? $this->options['options'] : array();
            if (!empty($this->options['adapter'])) {
                $this->adapter = new $this->options['adapter']($options);
            } else {
                $this->adapter = new DefaultAdapter($options);
            }
        }

        return $this->adapter;
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
            if (!empty($this->options['resource'][$name])) {
                if (is_string($this->options['resource'][$name])) {
                    $class = $this->options['resource'][$name];
                } else {
                    if (!empty($this->options['resource'][$name]['class'])) {
                        $class = $this->options['resource'][$name]['class'];
                    }
                    if (isset($this->options['resource'][$name]['options'])) {
                        $options =
                            $this->options['resource'][$name]['options'];
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
     * @param UserModel|int|string|null $identity
     *      User id, identity or data object
     * @param string                    $type
     *      Type of the identity: id, identity, object
     * @return self
     */
    public function bind($identity = null, $type = '')
    {
        if (null !== $identity || null === $this->model) {
            if ($identity instanceof UserModel) {
                $this->model = $identity;
            } else {
                $this->model = $this->getAdapter()->getUser($identity, $type);
            }
            // Store current session user model for first time
            if (null === $this->modelSession) {
                $this->modelSession = $this->model;
            }

            // Bind user model to service adapter
            $this->getAdapter()->bind($this->model);
            // Bind user model to handlers
            foreach ($this->resource as $key => $handler) {
                if ($handler instanceof BindInterface) {
                    $handler->bind($this->model);
                }
            }
        }

        return $this;
    }

    /**
     * Restore user model to current session user
     *
     * @return self
     */
    public function restore()
    {
        $this->bind($this->modelSession);

        return $this;
    }

    /**
     * Destory current user session
     *
     * @return self
     */
    public function destroy()
    {
        $this->modelSession = false;
        $this->setPersist(false);

        return $this;
    }

    /**
     * Check if use has logged in
     *
     * @return bool
     * @api
     */
    public function hasIdentity()
    {
        return $this->modelSession && $this->modelSession->get('id')
            ? true : false;
    }

    /**
     * Get identity of current logged user
     *
     * @param bool $asId    Return use id as identity or user identity name
     *
     * @return null|int|string
     * @api
     */
    public function getIdentity($asId = true)
    {
        if (!$this->hasIdentity()) {
            $identity = null;
        } else {
            $identity = $asId
                ? $this->modelSession->id
                : $this->modelSession->identity;
        }

        return $identity;
    }

    /**
     * Get get resource handler or user variables
     *
     * @param string $var
     * @return AbstractResource|mixed
     */
    public function __get($var)
    {
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
                $result = $this->getAdapter()->{$var};
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
                $result = call_user_func_array(
                    array($this->getAdapter(), $method),
                    $args
                );
                break;
        }

        return $result;
    }

    /**
     * Set user persist profile
     *
     * Persistent user profile data
     *
     *  - uid: user id
     *  - identity: identity or username
     *  - name: user full name or display name
     *  - email: email
     *  - <extra fields>: specified by each adapter
     *
     * @param array|bool $data
     * @return self
     */
    public function setPersist($data = array())
    {
        $_SESSION['PI_USER'] = $data ? (array) $data : null;

        return $this;
    }

    /**
     * Get user persist profile
     *
     * @param string|null $name
     * @return mixed|null
     */
    public function getPersist($name = null)
    {
        if (!$this->hasIdentity()) {
            return false;
        }
        $data = (array) $_SESSION['PI_USER'];
        if ($name) {
            $result = isset($data[$name]) ? $data[$name] : null;
        } else {
            $result = $data;
        }

        return $result;
    }
}
