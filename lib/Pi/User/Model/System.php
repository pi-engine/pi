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
            $data = array();
        }
        $this->assign($data);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function loadRole()
    {
        if ($uid = $this->get('id')) {
            $row = Pi::model('user_role')->select(array(
                'uid'       => $uid,
                'section'   => Pi::engine()->application()->getSection(),
            ))->current();
            $this->role = $row ? $row['role'] : 'guest';
        } else {
            $this->role = 'guest';
        }

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
    public function isAdmin()
    {
        return 'admin' == $this->role() ? true : false;
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
