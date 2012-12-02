<?php
/**
 * Authentication service class
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
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Application\Service;
use Pi;
use Pi\Application\User;
use Zend\Authentication\Adapter;
use Zend\Authentication\Storage;

/**
 * Authentication service class
 *
 * Usage with default adapter:
 * <code>
 *  Pi::service('authentication')->authenticate('user', 'password');
 *  if ($rememberMe) {
 *      Pi::registry('session')->rememberMe();
 *  }
 * </code>
 * Usage with specified adapter:
 * <code>
 *  $adapter = new Adapter();
 *  Pi::service('authentication')->authenticate('user', 'password', $adapter);
 *  if ($rememberMe) {
 *      Pi::registry('session')->rememberMe();
 *  }
 * </code>
 * or
 * Usage with default adapter:
 * <code>
 *  $adapter = new Adapter();
 *  Pi::serivce('authentication')->setAdapter($adapter);
 *  Pi::service('authentication')->authenticate('user', 'password');
 *  if ($rememberMe) {
 *      Pi::registry('session')->rememberMe();
 *  }
 * </code>
 *
 * @see Zend\Authentication\AuthenticationService
 */
class Authentication extends AbstractService
{
    /**
     * Config file identifier
     *
     * @var string
     */
    protected $fileIdentifier = 'authentication';

    /**
     * Adpater handler
     * @var Adapter
     */
    protected $adapter;
    /**
     * Storage handler
     * @var Storage
     */
    protected $storage;

    /**
     * Authenticates against the supplied adapter
     *
     * @param string $identity
     * @param string $credential
     * @param Adapter $adapter
     * @return Zend\Authentication\Result
     */
    public function authenticate($identity, $credential, Adapter $adapter = null)
    {
        $adapter = $adapter ?: $this->getAdapter();
        $adapter->setIdentity($identity);
        $adapter->setCredential($credential);
        $result = $adapter->authenticate();

        if ($this->hasIdentity()) {
            $this->clearIdentity();
        }

        if ($result->isValid()) {
            $this->getStorage()->write($result->getIdentity());
        }

        return $result;
    }

    public function setAdapter(Adapter $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    public function getAdapter()
    {
        if (!$this->adapter) {
            $class      = $this->options['adapter']['class'];
            $options    = isset($this->options['adapter']['options']) ? $this->options['adapter']['options'] : null;
            $this->adapter = new $class($options);
        }
        return $this->adapter;
    }

    public function setStorage(Storage $storage)
    {
        $this->storage = $storage;
        return $this;
    }

    public function getStorage()
    {
        if (!$this->storage) {
            $class      = $this->options['storage']['class'];
            $options    = isset($this->options['storage']['options']) ? $this->options['storage']['options'] : null;
            $this->storage = new $class($options);
        }
        return $this->storage;
    }

    /**
     * Returns true if and only if an identity is available from storage
     *
     * @return boolean
     */
    public function hasIdentity()
    {
        return !$this->getStorage()->isEmpty();
    }

    /**
     * Returns the identity from storage or null if no identity is available
     *
     * @return mixed|null
     */
    public function getIdentity()
    {
        $storage = $this->getStorage();

        if ($storage->isEmpty()) {
            return null;
        }

        return $storage->read();
    }

    /**
     * Clears the identity from persistent storage
     *
     * @return void
     */
    public function clearIdentity()
    {
        $this->getStorage()->clear();
    }

    /**
     * Wake up a user
     *
     * @param string|null $identity
     */
    public function wakeup($identity = null)
    {
        $identity = $identity ?: $this->getIdentity();
        Pi::registry('user', new User($identity));
    }
}
