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
 * Basic APIs defined by `\Pi\User\Adapter\AbstractAdapter`
 * called via magic method __call()
 * ----------------------------------------------------------------------------
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
 * + Avatar
 *   - avatar([$id])
 *   - avatar([$id])->setSource($source)
 *   - avatar([$id])->get([$size[, $attributes[, $source]]])
 *   - avatar([$id])->getList($ids[, $size[, $attributes[, $source]]])
 *   - avatar([$id])->set($value[, $source])
 *   - avatar([$id])->delete()
 *
 * + Message
 *   - message([$id])
 *   - message([$id])->send($message, $from)
 *   - message([$id])->notify($message, $subject[, $tag])
 *   - message([$id])->getCount()
 *   - message([$id])->getAlert()
 *
 * + Timeline/Activity
 *   - timeline([$id])
 *   - timeline([$id])->get($limit[, $offset[, $condition]])
 *   - timeline([$id])->getCount([$condition]])
 *   - timeline([$id])->add($message, $module[, $tag[, $time]])
 *   - timeline([$id])->getActivity($name, $limit[, $offset[, $condition]])
 *   - timeline([$id])->delete([$condition])
 *
 * + Relation
 *   - relation([$id])
 *   - relation([$id])->get($relation, $limit, $offset, $condition, $order)
 *   - relation([$id])->getCount($relation[, $condition]])
 *   - relation([$id])->hasRelation($uid, $relation)
 *   - relation([$id])->add($uid, $relation)
 *   - relation([$id])->delete([$uid[, $relation]])
 *
 * @see \Pi\User\Adapter\AbstractAdapter for detailed user specific APIs
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
     * @var UserModel|null|false
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
     * Get resource handler
     *
     * @param string $name
     * @param int|null $id
     * @return AbstractResource
     */
    public function getResource($name, $id = null)
    {
        if (!$this->resource[$name] instanceof AbstractResource) {
            $options = array();
            $class = '';
            if (!empty($this->options['resource'][$name])) {
                if (is_string($this->options['resource'][$name])) {
                    $class = $this->options['resource'][$name];
                } else {
                    if (isset($this->options['resource'][$name]['class'])) {
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
            $this->resource[$name]->bind($this->getUser($id));
            if ($options) {
                $this->resource[$name]->setOptions($options);
            }
        } elseif (null !== $id) {
            $this->resource[$name]->bind($this->getUser($id));
        }

        return $this->resource[$name];
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
                $this->model = $this->getUser($identity, $type);
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
     * @param bool $asId Return use id as identity or user identity name
     * @return null|int|string
     * @api
     */
    public function getIdentity($asId = true)
    {
        if (!$this->hasIdentity()) {
            $identity = null;
        } else {
            $identity = $asId
                ? $this->modelSession->getId()
                : $this->modelSession->getIdentity();
        }

        return $identity;
    }

    /**
     * Get user variables
     *
     * @param string $var
     * @return mixed
     */
    public function __get($var)
    {
        return $this->getAdapter()->{$var};
    }

    /**
     * Method adapter allows a shortcut
     *
     * Call APIs defined in {@link \Pi\User\Adapter\AbstractAdapter}
     *
     * @param  string  $method
     * @param  array  $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->getAdapter(), $method),
            $args);
    }

    /**
     * Get avatar handler
     *
     * @param int|null $id
     * @return AbstractResource
     */
    public function avatar($id = null)
    {
        return $this->getResource('avatar', $id);
    }

    /**
     * Get message handler
     *
     * @param int|null $id
     * @return AbstractResource
     */
    public function message($id = null)
    {
        return $this->getResource('message', $id);
    }

    /**
     * Get timeline handler
     *
     * @param int|null $id
     * @return AbstractResource
     */
    public function timeline($id = null)
    {
        return $this->getResource('timeline', $id);
    }

    /**
     * Get relation handler
     *
     * @param int|null $id
     * @return AbstractResource
     */
    public function relation($id = null)
    {
        return $this->getResource('relation', $id);
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
     * @param array|false $data
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
