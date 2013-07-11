<?php
/**
 * Pi Engine local user model class
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
 * @package         Pi\User
 */

namespace Pi\User\Model;

use Pi;
use Pi\Acl\Acl;
use StdClass;

class Local extends AbstractModel
{
    /**
     * {@inheritDoc}
     */
    public function load($data, $column = 'id')
    {
        $model = Pi::model('user');

        if ('id' == $column) {
            $user = $model->find(intval($data));
        } else {
            $user = $model->select(array($column => $data))->current();
        }
        if ($user && $user->active) {
            $this->assign($user);
        }
        $this->role = null;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function loadRole()
    {
        if ($this->account->id) {
            $model = ('admin' == Pi::engine()->section()) ? Pi::model('user_staff') : Pi::model('user_role');
            $role = $model->find($this->account->id, 'user');
            $this->role = $role ? $role->role : Acl::GUEST;
        } else {
            $this->role = Acl::GUEST;
        }
        return $this->role;
    }

    /**
     * {@inheritDoc}
     */
    public function loadProfile()
    {
        $row = Pi::model('user_profile')->find($this->id);
        $this->profile = $row ? (object) $row->toArray() : new StdClass;
        return $this->profile;
    }

    /**
     * {@inheritDoc}
     */
    public function isGuest()
    {
        return $this->account->id ? false : true;
    }

    /**
     * {@inheritDoc}
     */
    public function isAdmin()
    {
        return $this->role() == Acl::ADMIN ? true : false;
    }

    /**
     * {@inheritDoc}
     */
    public function isMember()
    {
        return $this->hasRole(Acl::MEMBER)  ? true : false;
    }

    /**
     * {@inheritDoc}
     */
    public function isStaff()
    {
        return $this->hasRole(Acl::STAFF)  ? true : false;
    }

    /**
     * {@inheritDoc}
     */
    public function hasRole($role)
    {
        $roles = Pi::service('registry')->role->read($this->role());
        return in_array($role, $roles) ? true : false;
    }
}
