<?php
/**
 * Pi Engine user service gateway
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Application
 * @subpackage      Service
 */

namespace Pi\Application\Service;

use Pi;
use Pi\User\BindInterface;
use Pi\User\Handler\AbstractHandler;
use Pi\User\Adapter\AbstractAdapter;
use Pi\User\Adapter\Local as LocalAdapter;
use Pi\User\Model\AbstractModel as UserModel;


/**
 * User service
 *
 * Serves as a gateway to user account and profile data,
 * to proxy APIs to corresponding adapter, either Pi built-in user or Pi SSO or any third-party user service
 */

/**
 * User APIs
 *
 * + Meta operations
 *   - getMeta([$type])                                     // Get meta list of user, type: account, profile, extra - extra profile non-structured
 *
 * + User operations
 *   + Bind
 *   - bind($id[, $field])                                  // Bind current user
 *   - restore()                                            // Restore bound user to current session user
 *
 *   + Read
 *   - getUser([$id])                                       // Get current user or specified user
 *   - getUserList($ids)                                    // List of users by ID list
 *   - getIds($condition[, $limit[, $offset[, $order]]])    // ID list subject to $condition
 *   - getCount([$condition])                               // User count to $condition
 *
 *   + Add
 *   - addUser($data)               // Add a new user with account and profile
 *   + Update
 *   - updateUser($data[, $id])     // Update a user for account and profile
 *   + Delete
 *   - deleteUser($id)              // Delete a user
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
 * + External APIs
 * + Avatar
 *   - avatar([$id])                                                                // Get avatar handler
 *   - avatar([$id])->setSource($source)                                            // Set avatar source: upload, gravatar, local, empty for auto
 *   - avatar([$id])->get([$size[, $attributes[, $source]]])                        // Get avatar of a user
 *   - avatar([$id])->getList($ids[, $size[, $attributes[, $source]]])              // Get avatars of a list of users
 *   - avatar([$id])->set($value[, $source])                                        // Set avatar for a user
 *   - avatar([$id])->delete()                                                      // Delete user avatar
 *
 * + Message
 *   - message([$id])                                                               // Get message handler
 *   - message([$id])->send($message, $from)                                        // Send message to a user
 *   - message([$id])->notify($message, $subject[, $tag])                           // Send notification to a user
 *   - message([$id])->getCount()                                                   // Get message total count of current user
 *   - message([$id])->getAlert()                                                   // Get message alert (new) count of current user
 *
 * + Timeline/Activity
 *   - timeline([$id])                                                              // Get timeline handler
 *   - timeline([$id])->get($limit[, $offset[, $condition]])                        // Get timeline list
 *   - timeline([$id])->getCount([$condition]])                                     // Get timeline count subject to condition
 *   - timeline([$id])->add($message, $module[, $tag[, $time]])                     // Add activity to user timeline
 *   - timeline([$id])->getActivity($name, $limit[, $offset[, $condition]])         // Get activity list of a user
 *   - timeline([$id])->delete([$condition])                                        // Delete timeline items subjecto to condition
 *
 * + Relation
 *   - relation([$id])                                                              // Get relation handler
 *   - relation([$id])->get($relation, $limit[, $offset[, $condition[, $order]]])   // Get IDs with relationship: friend, follower, following
 *   - relation([$id])->getCount($relation[, $condition]])                          // Get count with relationship: friend, follower, following
 *   - relation([$id])->hasRelation($uid, $relation)                                // Check if $id has relation with $uid: friend, follower, following
 *   - relation([$id])->add($uid, $relation)                                        // Add $uid as a relation: friend, follower, following
 *   - relation([$id])->delete([$uid[, $relation]])                                 // Delete $uid as relation: friend, follower, following
 */
class User extends AbstractService
{
    protected $fileIdentifier = 'user';

    /**
     * Bound user data object
     * @var UserModel
     */
    protected $model;

    /**
     * User data object of current session
     * @var UserModel
     */
    protected $modelSession;

    /**
     * Service handler adapter
     * @var AbstractAdapter
     */
    protected $adapter;

    /**
     * External handlers
     *
     * @var array
     */
    protected $handler = array(
        'avatar'    => null,
        'message'   => null,
        'timeline'  => null,
        'relation'  => null,
    );

    /**
     * Set service adapter
     *
     * @param AbstractAdapter $adapter
     * @return User
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
            if (!empty($this->options['adapter'])) {
                $this->adapter = new $this->options['adapter'];
            } else {
                $this->adapter = new LocalAdapter;
            }
        }
        return $this->adapter;
    }

    /**
     * Get external handler
     *
     * @param string $name
     * @param int|null $id
     * @return AbstractHandler
     */
    public function getHandler($name, $id = null)
    {
        if (!$this->handler[$name] instanceof AbstractHandler) {
            if (!empty($this->options['handler'][$name])) {
                $class = $this->$this->options['handler'][$name];
            } else {
                $class = 'Pi\User\Handler\\' . ucfirst($name);
            }
            $this->handler[$name] = new $class;
            $this->handler[$name]->bind($this->getUser($id));
        } elseif (null !== $id) {
            $this->handler[$name]->bind($this->getUser($id));
        }

        return $this->handler[$name];
    }

    /**
     * Get avatar handler
     *
     * @param int|null $id
     * @return AbstractHandler
     */
    public function avatar($id = null)
    {
        return $this->getHandler('avatar', $id);
    }

    /**
     * Get message handler
     *
     * @param int|null $id
     * @return AbstractHandler
     */
    public function message($id = null)
    {
        return $this->getHandler('message', $id);
    }

    /**
     * Get timeline handler
     *
     * @param int|null $id
     * @return AbstractHandler
     */
    public function timeline($id = null)
    {
        return $this->getHandler('timeline', $id);
    }

    /**
     * Get relation handler
     *
     * @param int|null $id
     * @return AbstractHandler
     */
    public function relation($id = null)
    {
        return $this->getHandler('relation', $id);
    }

    /**
     * Bind a user to service
     *
     * @param UserModel|int|string|null $identity   User id, identity or data object
     * @param string                    $type       Type of the identity: id, identity, object
     * @return User
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
            foreach ($this->handler as $key => $handler) {
                if ($handler instanceof BindInterface) {
                    $handler->bind($this->model);
                }
            }
        }

        return $this;
    }

    /**
     * Restore user model to current session user
     */
    public function restore()
    {
        $this->bind($this->modelSession);
        return $this;
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
     * @param  string  $method
     * @param  array  $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->getAdapter(), $method), $args);
    }
}
