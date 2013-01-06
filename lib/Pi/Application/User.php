<?php
/**
 * Pi Engine User class
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
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Application;

use Pi;
use Pi\Acl\Acl;
use StdClass;

class User
{
    /**
     * Use account property
     * @var StdClass
     */
    protected $account;
    /**
     * User role
     * @var string
     */
    protected $role;
    /**
     * User profile property
     * @var StdClass
     */
    protected $profile;

    /**
     * Constructor
     *
     * @param array|int|string|null $data
     */
    public function __construct($data = null)
    {
        $this->account = (object) array(
            'id'        => 0,
            'identity'  => '',
            'email'     => '',
            'name'      => '',
        );

        if (is_array($data)) {
            $this->assign($data);
        } elseif (is_scalar($data)) {
            $this->load($data);
        }
    }

    /**
     * Magic method to access properties
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this))
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
     * Load user account from database
     *
     * @param int|string $data
     * @return User
     */
    public function load($data)
    {
        $model = Pi::model('user');
        if (is_numeric($data)) {
            $user = $model->find($data);
        } else {
            $user = $model->select(array('identity' => $data))->current();
        }
        if ($user && $user->active) {
            $this->assign($user);
        }
        $this->role = null;

        return $this;
    }

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
     * @param null|string|true $role null: return current role; string: set role; true - retrieve from DB
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

    public function loadRole()
    {
        if ($this->account->id) {
            $model = ('admin' == Pi::engine()->section()) ? Pi::model('user_staff') : Pi::model('user_role');
            $role = $model->find($this->account->id, 'user');
            $this->role = $role ? $role->role : Acl::GUEST;
        } else {
            $this->role = Acl::GUEST;
        }
    }

    /**
     * Retrieve profile object
     *
     * @return StdClass
     */
    public function profile()
    {
        if (null === $this->profile) {
            $row = Pi::model('user_profile')->find($this->id);
            $this->profile = $row ? (object) $row->toArray() : new StdClass;
        }

        return $this->profile;
    }

    /**
     * Check if current user is a guest
     *
     * @return bool
     */
    public function isGuest()
    {
        return $this->account->id ? false : true;
    }

    /**
     * Check if current user is a top admin
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role() == Acl::ADMIN ? true : false;
    }

    /**
     * Check if current user is a regular member
     *
     * @return bool
     */
    public function isMember()
    {
        return $this->hasRole(Acl::MEMBER)  ? true : false;
    }

    /**
     * Check if current user is a staff
     *
     * @return bool
     */
    public function isStaff()
    {
        return $this->hasRole(Acl::STAFF)  ? true : false;
    }

    /**
     * Check if current user has a role in its role ancestors
     *
     * @param string $role
     * @return bool
     */
    public function hasRole($role)
    {
        $roles = Pi::service('registry')->role->read($this->role());
        return in_array($role, $roles) ? true : false;
    }
}
