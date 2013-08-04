<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\User\Model;

use Pi;
use StdClass;

/**
 * Abstract user model
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractModel
{
    /**
     * Use account property
     * @var StdClass
     */
    protected $account;

    /** @var array User account meta */
    protected $accountMeta = array(
        'id'        => 0,
        'identity'  => '',
        'email'     => '',
    );

    /**
     * User profile property
     *
     * @var StdClass
     */
    protected $profile;

    /** @var array User profile meta */
    protected $profileMeta = array(
        'id'        => 0,
        'uid'       => 0,
        'name'      => '',
    );

    /**
     * User role
     * @var string
     */
    protected $role;

    /**
     * Constructor
     *
     * @param array|int|string|null $data
     * @param string $column
     */
    public function __construct($data = null, $column = 'id')
    {
        $this->account = $this->createAccount();

        if (is_array($data)) {
            $this->assign($data);
        } elseif (is_scalar($data)) {
            $this->load($data, $column);
        }
    }

    /**
     * Create account object
     *
     * @param array $vars
     * @return StdClass
     */
    public function createAccount($vars = array())
    {
        $account = array_merge($this->accountMeta, $vars);
        return (object) $account;
    }

    /**
     * Magic method to access properties
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        switch ($name) {
            case 'account':
                return $this->account;
            case 'role':
            case 'profile':
                return $this->$name();
                break;
            default:
                if (isset($this->account->$name)) {
                    return $this->account->$name;
                } elseif (isset($this->profile->$name)) {
                    return $this->profile->$name;
                }
                break;
        }
    }

    /**
     * Get an attribute
     *
     * @param string $name
     * @return mixed
     */
    public function get($name)
    {
        return $this->$name;
    }

    /**
     * Load user account from database
     *
     * @param int|string $data
     * @param string    $column
     * @return User
     */
    abstract public function load($data, $column = 'id');

    /**
     * Assign account data to current user
     *
     * @param array|object $data
     * @return User
     */
    public function assign($data)
    {
        // Convert to array
        if (is_object($data)) {
            $data = $data->toArray();
        }
        // Set account property
        foreach ($this->account as $col => &$val) {
            if (isset($data[$col])) {
                $val = $data[$col];
            }
        }

        // Set role
        if (isset($data['role'])) {
            $this->role($data['role']);
        } else {
            $this->role = null;
        }

        return $this;
    }

    /**
     * Set role or retrieve
     *
     * @param null|string|true $role
     *      null: return current role;
     *      string: set role;
     *      true - retrieve from DB
     * @return User|string
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
     * Load role of current user
     *
     * @return string
     */
    abstract public function loadRole();

    /**
     * Retrieve profile object
     *
     * @return StdClass
     */
    public function profile()
    {
        if (null === $this->profile) {
            $this->loadProfile();
        }

        return $this->profile;
    }

    /**
     * Load profile of current user
     */
    abstract public function loadProfile();

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
     * Check if current user is a regular member
     *
     * @return bool
     */
    abstract public function isMember();

    /**
     * Check if current user is a staff
     *
     * @return bool
     */
    abstract public function isStaff();

    /**
     * Check if current user has a role in its role ancestors
     *
     * @param string $role
     * @return bool
     */
    abstract public function hasRole($role);
}
