<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\User\Adapter;

use Pi;
//use Pi\User\Model\Local as UserModel;

/**
 * Pi Engine local user service provided by user module
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Local extends System
{
    /**#@+
     * Meta operations
     */
    /**
     * {@inheritDoc}
     */
    public function getMeta($type = '', $action = '')
    {
        return Pi::api('user', 'user')->getMeta($type, $action);
    }
    /**#@-*/

    /**#@+
     * User operations
     */
    /**
     * {@inheritDoc}
     */
    public function getUser($uid = null, $field = 'id')
    {
        return parent::getUser($uid, $field);
    }

    /**
     * {@inheritDoc}
     */
    public function getUids(
        $condition  = array(),
        $limit      = 0,
        $offset     = 0,
        $order      = ''
    ) {
        $result = Pi::api('user', 'user')->getUids(
            $condition,
            $limit,
            $offset,
            $order
        );

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getList(
        array $condition  = array(),
        $limit      = 0,
        $offset     = 0,
        $order      = '',
        array $field  = array()
    ) {
        $result = Pi::api('user', 'user')->getList(
            $condition,
            $limit,
            $offset,
            $order,
            $field
        );

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getCount($condition = array())
    {
        $result = Pi::api('user', 'user')->getCount($condition);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function addUser($data, $setRole = true)
    {
        return Pi::api('user', 'user')->addUser($data, $setRole);
    }

    /**
     * {@inheritDoc}
     */
    public function updateUser($uid, $data)
    {
        return Pi::api('user', 'user')->updateUser($uid, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteUser($uid)
    {
        if ($this->isRoot($uid)) {
            return false;
        }
        return Pi::api('user', 'user')->deleteUser($uid);
    }

    /**
     * {@inheritDoc}
     */
    public function activateUser($uid)
    {
        return Pi::api('user', 'user')->activateUser($uid);
    }

    /**
     * {@inheritDoc}
     */
    public function enableUser($uid)
    {
        return Pi::api('user', 'user')->enableUser($uid);
    }

    /**
     * {@inheritDoc}
     */
    public function disableUser($uid)
    {
        if ($this->isRoot($uid)) {
            return false;
        }
        return Pi::api('user', 'user')->disableUser($uid);
    }
    /**#@-*/

    /**#@+
     * User account/Profile fields operations
     */
    /**
     * {@inheritDoc}
     */
    public function get(
        $uid,
        $field = array(),
        $filter = false,
        $activeOnly = false
    ) {
        return Pi::api('user', 'user')->get(
            $uid,
            $field,
            $filter,
            $activeOnly
        );
    }

    /**
     * {@inheritDoc}
     */
    public function mget(
        array $uids,
        $field = array(),
        $filter = false,
        $activeOnly = false
    ) {
        return Pi::api('user', 'user')->mget(
            $uids,
            $field,
            $filter,
            $activeOnly
        );
    }

    /**
     * {@inheritDoc}
     */
    public function set($uid, $field, $value)
    {
        return Pi::api('user', 'user')->set($uid, $field, $value);
    }
    /**#@-*/

    /**
     * {@inheritDoc}
     */
    public function setRole($uid, $role)
    {
        return Pi::api('user', 'user')->setRole($uid, $role);
    }

    /**
     * {@inheritDoc}
     */
    public function revokeRole($uid, $role)
    {
        return Pi::api('user', 'user')->revokeRole($uid, $role);
    }

    /**
     * {@inheritDoc}
     */
    public function getRole($uid, $section = '')
    {
        return Pi::api('user', 'user')->getRole($uid, $section);
    }

    /**#@+
     * Utility APIs
     */
    /**
     * {@inheritDoc}
     */
    public function getRoute()
    {
        return Pi::api('user', 'user')->getRoute();
    }

    /**
     * {@inheritDoc}
     */
    public function getUrl($type, $var = null)
    {
        return Pi::api('user', 'user')->getUrl($type, $var);
    }

    /**
     * {@inheritDoc}
     */
    public function killUser($uid)
    {
        return parent::killUser($uid);
    }
    /**#@-*/

    /**
     * {@inheritDoc}
     */
    public function getUserModel($uid, $field = 'id')
    {
        $model = Pi::api('user', 'user')->getUser($uid, $field);

        return $model;
    }
}
