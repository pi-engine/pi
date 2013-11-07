<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Authentication\Strategy;

use Pi;
use Pi\Authentication\Adapter\AdapterInterface;
use Pi\Authentication\Storage\StorageInterface;
use Zend\Authentication\Result as AuthenticationResult;

/**
 * Pi authentication strategy interface
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractStrategy
{
    /** @var array Options */
    protected $options = array();

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
     * Returns the identity from storage or null if no identity is available
     *
     * @return mixed|null
     */
    abstract public function getIdentity();

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
     *
     * @return AuthenticationResult
     */
    abstract public function authenticate($identity, $credential);

    /**
     * Check if authenticated and go to authentication process if not
     *
     * @param array $params
     *
     * @return bool
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
