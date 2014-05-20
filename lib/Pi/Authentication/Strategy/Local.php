<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Service
 */

namespace Pi\Authentication\Strategy;

use Pi;
use Pi\Authentication\Adapter\AdapterInterface;
use Pi\Authentication\Storage\StorageInterface;

/**
 * Authentication strategy
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Local extends AbstractStrategy
{
    /**
     * {@inheritDoc}
     */
    protected $fileIdentifier = 'local';

    /**
     * {@inheritDoc}
     */
    protected $name = 'local';

    /**
     * Adapter handler
     *
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * {@inheritDoc}
     */
    public function getUrl($type, $params = null)
    {
        switch ($type) {
            case 'login':
            case 'logout':
                if ($params && is_string($params)) {
                    $params = array(
                        'redirect' => $params,
                    );
                }
                $url = Pi::service('user')->getUrl($type, $params);
                break;
            default:
                $url = '';
        }

        return $url;
    }

    /**
     * {@inheritDoc}
     */
    public function bind()
    {
        $identity = $this->getIdentity();
        Pi::service('user')->bind($identity, $this->getIdentityField());
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
        $adapter    = new $class;
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
     * {@inheritDoc}
     */
    public function hasIdentity()
    {
        return !$this->getStorage()->isEmpty();
    }

    /**
     * {@inheritDoc}
     */
    public function clearIdentity()
    {
        $this->getStorage()->clear();
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate($identity, $credential, $column = '')
    {
        $adapter = $this->getAdapter();
        if ($column && method_exists($adapter, 'setIdentityColumn')) {
            $adapter->setIdentityColumn($column);
        }
        $adapter->setIdentity($identity);
        $adapter->setCredential($credential);
        $result = $adapter->authenticate();

        if ($this->hasIdentity()) {
            $this->clearIdentity();
        }

        if ($result->isValid()) {
            $result->setData($adapter->getResultRow());
            $identity = $result->getData($this->getIdentityField());
            $this->getStorage()->write($identity);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function requireLogin(array $params = array())
    {
        if ($this->hasIdentity()) {
            return;
        }

        $this->login($params);
    }

    /**
     * {@inheritDoc}
     */
    public function login(array $params = array())
    {
        $url = Pi::service('user')->getUrl('login', $params);
        Pi::service('url')->redirect($url);
    }

    /**
     * {@inheritDoc}
     */
    public function logout(array $params = array())
    {
        $url = Pi::service('user')->getUrl('logout', $params);
        Pi::service('url')->redirect($url, true);
    }

    /**
     * {@inheritDoc}
     */
    public function getData(array $fields = array())
    {
        return Pi::service('user')->get(null, $fields);
    }
}
