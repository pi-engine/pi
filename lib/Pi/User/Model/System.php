<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\User\Model;

use Pi;

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
    public function get($name)
    {
        $result = null;
        if ('role' == $name) {
            $result = $this->role();
        } elseif (isset($this->data[$name])) {
            $result = $this->data[$name];
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function load($uid, $field = 'id')
    {
        if ($uid) {
            $row = Pi::model('user_account')->find($uid, $field);
            if ($row) {
                $data = $row->toArray();
                unset($data['credential'], $data['salt']);
            } else {
                $data = array();
            }
        } else {
            $data = $this->getGuest();
        }
        $this->assign($data);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function loadRole()
    {
        $this->role = Pi::service('user')->getRole(
            $this->get('id'),
            '',
            true
        );

        return $this->role;
    }

    /**
     * {@inheritDoc}
     */
    public function isGuest()
    {
        return $this->get('id') ? false : true;
    }

    /**
     * {@inheritDoc}
     */
    public function isRoot()
    {
        return Pi::service('permission')->isRoot($this->get('id'));
    }

    /**
     * {@inheritDoc}
     */
    public function isAdmin($module = '')
    {
        return Pi::service('permission')->isAdmin($module, $this->get('id'));
    }

    /**
     * {@inheritDoc}
     */
    public function hasRole($role)
    {
        return in_array($role, $this->role()) ? true : false;
    }
}
