<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Authentication\Strategy;

use Pi;
//use Pi\Authentication\Adapter\AdapterInterface;
use Pi\Authentication\Storage\StorageInterface;
use Zend\Authentication\Result as AuthenticationResult;

/**
 * Pi authentication strategy interface
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractStrategy
{
    /** @var string Identifier for file name of config data */
    protected $fileIdentifier = '';

    /** @var string Identifier for strategy */
    protected $name = '';

    /** @var array Options */
    protected $options = array();

    /**
     * Storage handler
     *
     * @var StorageInterface
     */
    protected $storage;

    /** @var string Field name for identity to be stored in storage */
    protected $identityField = 'id';

    /**
     * Constructor
     *
     * @param array $options Parameters to send to the service
     */
    public function __construct($options = array())
    {
        // Set specified options
        if ($options) {
            $this->setOptions($options);
            // Load default options from config file
        } elseif ($this->fileIdentifier) {
            $this->setOptions('authentication.' . $this->fileIdentifier . '.php');
        }
    }

    /**
     * Set options
     *
     * @param array|string $options Array of options or config file name
     * @return void
     */
    public function setOptions($options = array())
    {
        if (is_string($options)) {
            $options = Pi::config()->load($options) ?: array();
        }
        $this->options = $options;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set an option
     *
     * @param string $name
     * @param mixed $value
     *
     * @return $this
     */
    public function setOption($name, $value)
    {
        $this->options[$name] = $value;

        return $this;
    }

    /**
     * Get an option
     *
     * @return mixed|null
     */
    public function getOption()
    {
        $args = func_get_args();
        $result = $this->options;
        foreach ($args as $name) {
            if (!is_array($result)) {
                $result = null;
                break;
            }
            if (isset($result[$name])) {
                $result = $result[$name];
            } else {
                $result = null;
                break;
            }
        }

        return $result;
    }

    /**
     * Returns identity field name
     *
     * @return string
     */
    public function getIdentityField()
    {
        $field = $this->getOption('identity_field') ?: $this->identityField;

        return $field;
    }

    /**
     * Returns the identity from storage or false if no identity is available
     *
     * @return int|string|bool
     */
    public function getIdentity()
    {
        $identity = false;
        $storage = $this->getStorage();
        if (!$storage->isEmpty()) {
            $field  = $this->getIdentityField();
            $data   = $storage->read();
            if (is_scalar($data)) {
                $identity = $data;
            } elseif (is_array($data) && isset($data[$field])) {
                $identity = $data[$field];
            }
            if ($identity && 'id' == $field) {
                $identity = (int) $identity;
            }
        }

        return $identity;
    }

    /**
     * Get strategy name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get URIs
     *
     * @param string $type  Type for URI: login, logout
     * @param array|string $params
     *
     * @return string
     */
    abstract public function getUrl($type, $params = null);

    /**
     * Load current session user and bind to user service
     *
     * @return void
     */
    abstract public function bind();

    /**
     * Check if an identity in current session
     *
     * Returns true if and only if an identity is available from storage
     *
     * @return bool
     */
    abstract public function hasIdentity();

    /**
     * Clears the identity from persistent storage
     *
     * @return void
     */
    abstract public function clearIdentity();

    /**
     * Authenticates against the supplied adapter
     *
     * @param string $identity
     * @param string $credential
     * @param string $column Column name for identity to authenticate
     *
     * @return AuthenticationResult
     */
    abstract public function authenticate($identity, $credential, $column = '');

    /**
     * Check if authenticated and go to authentication process if not
     *
     * @param array $params
     *
     * @return void
     */
    abstract public function requireLogin(array $params = array());

    /**
     * Go to login process
     *
     * @param array $params
     *
     * @return void
     */
    abstract public function login(array $params = array());

    /**
     * Go to logout process
     *
     * @param array $params
     *
     * @return void
     */
    abstract public function logout(array $params = array());

    /**
     * Get user profile data from current session
     *
     * @param array $fields
     *
     * @return array
     */
    abstract public function getData(array $fields = array());
}
