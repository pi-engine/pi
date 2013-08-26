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
use Pi\Acl\Acl;
use StdClass;

/**
 * System user model
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class System extends AbstractModel
{
    /**
     * {@inheritDoc}
     */
    public function load($data, $column = 'id')
    {
        $model = Pi::model('user_account');

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
    public function loadProfile()
    {
        $this->profile = new StdClass;

        return $this->profile;
    }

    /**
     * {@inheritDoc}
     */
    public function loadRole()
    {
        if ($this->account->id) {
            $row = Pi::model('user_role')->select(array(
                'uid'       => $this->account->id,
                'section'   => 'front',
            ))->current();
            $this->role = $row ? $row['role'] : Acl::GUEST;
        } else {
            $this->role = Acl::GUEST;
        }

        return $this->role;
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
        $roles = Pi::registry('role')->read($this->role());

        return in_array($role, $roles) ? true : false;
    }
}
