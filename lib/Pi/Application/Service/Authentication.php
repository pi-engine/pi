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
use Zend\Authentication\Adapter;
use Zend\Authentication\Storage;

/**
 * Authentication service
 *
 * Usage with default adapter:
 *
 * ```
 *  Pi::service('authentication')->authenticate(<identity>, <credential>);
 *  if ($rememberMe) {
 *      Pi::registry('session')->rememberMe();
 *  }
 * ```
 *
 * Usage with specified adapter:
 *
 * ```
 *  $adapter = new Adapter();
 *  Pi::service('authentication')->authenticate(<identity>, <credential>, $adapter);
 *  if ($rememberMe) {
 *      Pi::registry('session')->rememberMe();
 *  }
 * ```
 *
 * Usage with specified adapter:
 *
 * ```
 *  $adapter = new Adapter();
 *  Pi::serivce('authentication')->setAdapter($adapter);
 *  Pi::service('authentication')->authenticate(<identity>, <credential>);
 *  if ($rememberMe) {
 *      Pi::registry('session')->rememberMe();
 *  }
 * ```
 *
 * @see Zend\Authentication\AuthenticationService
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Authentication extends AbstractService
{
    /**
     * {@inheritDoc}
     */
    protected $fileIdentifier = 'authentication';

    /**
     * Adpater handler
     *
     * @var Adapter
     */
    protected $adapter;

    /**
     * Storage handler
     *
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

    /**
     * Set adapter
     *
     * @param Adapter $adapter
     * @return $this
     */
    public function setAdapter(Adapter $adapter)
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * Get adapter
     *
     * @return Adapter
     */
    public function getAdapter()
    {
        if (!$this->adapter) {
            $class      = $this->options['adapter']['class'];
            $options    = isset($this->options['adapter']['options']) ? $this->options['adapter']['options'] : null;
            $this->adapter = new $class($options);
        }
        return $this->adapter;
    }

    /**
     * Set storage
     * @param Storage $storage
     * @return $this
     */
    public function setStorage(Storage $storage)
    {
        $this->storage = $storage;
        return $this;
    }

    /**
     * Get storage
     *
     * @return Stroage
     */
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
     * Check if an identity in current session
     *
     * Returns true if and only if an identity is available from storage
     *
     * @return bool
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
}
