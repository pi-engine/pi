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
use Pi\User\Adapter\AbstractAdapter;
use Pi\User\Adapter\Local as LocalAdapter;
use Pi\User\Model\AbstractModel as UserModel;

/**
 * User service
 *
 * Serves as a gateway to user account and profile data,
 * to proxy APIs to corresponding user models, either Pi built-in user or Pi SSO or any third-party user service
 *
 * <ul>
 *  <li> Account
 *      <code>
 *      </code>
 *  </li>
 *  <li> Profile
 *      <code>
 *      </code>
 *  </li>
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
     * Previous user data object
     * @var UserModel
     */
    protected $modelPrevious;

    /**
     * Service handler adapter
     * @var AbstractAdapter
     */
    protected $adapter;

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
            $this->modelPrevious = $this->model;
            if ($identity instanceof UserModel) {
                $this->model = $identity;
            } else {
                $this->model = $this->getUser($identity, $type);
            }
            $this->getAdapter()->bind($this->model);
        }

        return $this;
    }

    /**
     * Restore user model to previous
     */
    public function restore()
    {
        $this->model = $this->modelPrevious;
        $this->getAdapter()->bind($this->model);
        $this->modelPrevious = null;
        return $this;
    }

    /**
     * Get user data object
     *
     * @param UserModel|int|string|null  $identity   User id, identity or data object
     * @param string                    $field      Field of the identity: id, identity, object
     * @return UserModel
     */
    public function getUser($identity = null, $field = 'id')
    {
        return $this->getAdapter()->getUser($identity, $field);
    }

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
            if (!empty($this->options['adapter']) && class_exists($this->options['adapter'])) {
                $this->adapter = new $this->options['adapter'];
            } else {
                $this->adapter = new LocalAdapter;
            }
        }
        return $this->adapter;
    }

    /**#@+
     * Service adapter APIs
     * @see Pi\User\Adapter\AbstractAdapter
     */
    /**
     * Get user profile URL
     *
     * @param int $id
     * @return string
     */
    public function getProfileUrl($id = null)
    {
        return $this->getAdapter()->getProfileUrl($id);
    }

    /**
     * Get user full name
     *
     * @param int $id
     * @return string
     */
    public function getName($id = null)
    {
        return $this->getAdapter()->getName($id);
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
    /**#@-*/
}
