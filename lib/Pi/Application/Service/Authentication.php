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
use Pi\Authentication\Adapter\AdapterInterface;
use Pi\Authentication\Storage\StorageInterface;
use Zend\Authentication\Result;

/**
 * Authentication service
 *
 * Usage with default adapter:
 *
 * ```
 *  Pi::service('authentication')->authenticate(<identity>, <credential>);
 *  if ($rememberMe) {
 *      Pi::service('session')->rememberMe();
 *  }
 * ```
 *
 * Usage with specified adapter via authenticate method:
 *
 * ```
 *  $adapter = new Adapter();
 *  Pi::service('authentication')->authenticate(<identity>,
 *      <credential>, $adapter);
 *  if ($rememberMe) {
 *      Pi::service('session')->rememberMe();
 *  }
 * ```
 *
 * Usage with specified adapter via separate methods:
 *
 * ```
 *  $adapter = new Adapter();
 *  Pi::service('authentication')->setAdapter($adapter);
 *  Pi::service('authentication')->authenticate(<identity>, <credential>);
 *  if ($rememberMe) {
 *      Pi::service('session')->rememberMe();
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
     * Adapter handler
     *
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * Storage handler
     *
     * @var StorageInterface
     */
    protected $storage;

    /**
     * Authenticates against the supplied adapter
     *
     * @param string $identity
     * @param string $credential
     * @param AdapterInterface $adapter
     * @param StorageInterface $storage
     * @return Result
     */
    public function authenticate(
        $identity,
        $credential,
        AdapterInterface $adapter = null,
        StorageInterface $storage = null
    ) {
        $adapter = $adapter ?: $this->getAdapter();
        $adapter->setIdentity($identity);
        $adapter->setCredential($credential);
        $result = $adapter->authenticate();

        if ($this->hasIdentity()) {
            $this->clearIdentity();
        }

        if ($result->isValid()) {
            $storage = $storage ?: $this->getStorage();
            $storage->write($result->getIdentity());
            $result->setData($adapter->getResultRow());
        }

        return $result;
    }

    /**
     * Set adapter
     *
     * @param AdapterInterface $adapter
     * @return $this
     */
    public function setAdapter(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * Get adapter
     *
     * @return AdapterInterface
     */
    public function getAdapter()
    {
        if (!$this->adapter) {
            $this->adapter = $this->loadAdapter($this->options['adapter']);
        }

        return $this->adapter;
    }

    /**
     * Set storage
     *
     * @param StorageInterface $storage
     * @return $this
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     * Get storage
     *
     * @return StorageInterface
     */
    public function getStorage()
    {
        if (!$this->storage) {
            $this->storage = $this->loadStorage($this->options['storage']);
        }

        return $this->storage;
    }

    /**
     * Load authentication adapter
     *
     * @param array $config
     * @return AdapterInterface
     */
    public function loadAdapter($config = array())
    {
        $class      = $config['class'];
        $options    = isset($config['options']) ? $config['options'] : array();
        $adapter = new $class;
        if ($options) {
            $adapter->setOptions($options);
        }

        return $adapter;
    }

    /**
     * Load authentication storage
     *
     * @param array $config
     * @return StorageInterface
     */
    public function loadStorage($config = array())
    {
        $class      = $config['class'];
        $options    = isset($config['options']) ? $config['options'] : array();
        $storage = new $class($options);

        return $storage;
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
