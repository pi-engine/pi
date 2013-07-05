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
use Pi\User\AbstractService as AbstractHandler;
use Pi\User\Service as LocalHandler;

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
     * Bound user identity
     * @var string
     */
    protected $identity;

    /**
     * Service handler
     * @var AbstractHandler
     */
    protected $handler;

    /**
     * Bind a user to service
     *
     * @param string $identity
     * @return User
     */
    public function bind($identity = null)
    {
        if (null === $identity) {
            $identity = Pi::service('authentication')->getIdentity();
        }
        $this->identity = $identity;
        $this->getHandler()->bind($this->identity);

        return $this;
    }

    /**
     * Set service handler
     *
     * @param AbstractHandler $handler
     * @return User
     */
    public function setHandler(AbstractHandler $handler)
    {
        $this->handler = $handler;
        return $this;
    }

    /**
     * Get service handler
     *
     * Instantiate local handler if not available
     *
     * @return AbstractHandler
     */
    public function getHandler()
    {
        if (!$this->handler instanceof AbstractHandler) {
            if (!empty($this->options['handler']) && class_exists($this->options['handler'])) {
                $this->handler = new $this->options['handler'];
            } else {
                $this->handler = new LocalHandler;
            }
        }
        return $this->handler;
    }

    /**#@+
     * Service handler APIs
     * @see Pi\User\ServiceInterface
     */
    /**
     * Get user profile URL
     *
     * @param string $identity
     * @return string
     */
    public function getProfileUrl($identity = null)
    {
        $identity = $identity ?: $this->identity;
        return $this->getHandler()->getProfileUrl($identity);
    }

    /**
     * Get user full name
     *
     * @param string $identity
     * @return string
     */
    public function getName($identity = null)
    {
        $identity = $identity ?: $this->identity;
        return $this->getHandler()->getName($identity);
    }

    /**
     * Method handler allows a shortcut
     *
     * @param  string  $method
     * @param  array  $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->getHandler(), $method), $args);
    }
    /**#@-*/
}
