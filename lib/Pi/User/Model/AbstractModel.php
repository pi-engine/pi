<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\User\Model;

use ArrayObject;

/**
 * Abstract user model
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractModel extends ArrayObject
{
    /**
     * Use property
     * @var array
     */
    protected $data;

    /**
     * User role
     * @var string
     */
    protected $role;

    /**
     * Constructor
     *
     * @param array|int|string|null $data
     * @param string                $field
     *
     * @return \Pi\User\Model\AbstractModel
     */
    public function __construct($data = null, $field = 'id')
    {
        $this->data = array();

        if (is_array($data)) {
            $this->assign($data);
        } elseif (is_scalar($data)) {
            $this->load($data, $field);
        }
    }

    /**
     * Whether a offset exists
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        if ('role' == $offset) {
            $result = null === $this->role ? false : true;
        } else {
            $result = (null !== $this->data && isset($this->data[$offset]));
        }

        return $result;
    }

    /**
     * Offset to retrieve
     *
     * @param mixed $offset
     *
     * @return mixed|string
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Offset to set
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if ('role' == $offset) {
            $this->role = (string) $value;
        } else {
            $this->data[$offset] = $value;
        }
    }

    /**
     * Offset to unset
     *
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        if ('role' == $offset) {
            $this->role = null;
        } elseif (array_key_exists($offset, $this->data)) {
            unset($this->data[$offset]);
        }
    }

    /**
     * Get associative array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * Magic method to access properties
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->get($name);
    }

    /**
     * Assign account data to current user
     *
     * @param array $data
     * @return $this
     */
    public function assign(array $data)
    {
        // Set role
        if (isset($data['role'])) {
            $this->role($data['role']);
            unset($data['role']);
        } else {
            $this->role = null;
        }

        $this->data = $data;

        return $this;
    }

    /**
     * Set role or retrieve from DB
     *
     * @param null|string $role
     * @return $this|string
     */
    public function role($role = null)
    {
        if (is_string($role)) {
            $this->role = $role;
            return $this;
        }

        if (null === $this->role) {
            $this->loadRole();
        }

        return $this->role;
    }

    /**
     * Get an attribute
     *
     * @param string $name
     * @return mixed
     */
    abstract public function get($name);

    /**
     * Load user attributes
     *
     * @param int|string    $uid
     * @param string        $field
     * @return $this
     */
    abstract public function load($uid, $field = 'id');

    /**
     * Load role of current user
     *
     * @return string
     */
    abstract public function loadRole();

    /**
     * Check if current user is a guest
     *
     * @return bool
     */
    abstract public function isGuest();

    /**
     * Check if current user is a top admin
     *
     * @return bool
     */
    abstract public function isAdmin();

    /**
     * Check if current user has a role in its role ancestors
     *
     * @param string $role
     * @return bool
     */
    abstract public function hasRole($role);
}
