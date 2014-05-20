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

/**
 * Pi Engine Client user service provided by uclient module
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Client extends System
{
    /**#@+
     * Meta operations
     */
    /**
     * {@inheritDoc}
     */
    public function getMeta($type = '', $action = '')
    {
        return Pi::api('user', 'uclient')->getMeta($type, $action);
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
        $result = Pi::api('user', 'uclient')->getUids(
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
        $result = Pi::api('user', 'uclient')->getList(
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
        $result = Pi::api('user', 'uclient')->getCount($condition);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function addUser($data, $setRole = true)
    {
        return Pi::api('user', 'uclient')->addUser($data, $setRole);
    }

    /**
     * {@inheritDoc}
     */
    public function updateUser($uid, $data)
    {
        return Pi::api('user', 'uclient')->updateUser($uid, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteUser($uid)
    {
        if ($this->isRoot($uid)) {
            return false;
        }
        return Pi::api('user', 'uclient')->deleteUser($uid);
    }

    /**
     * {@inheritDoc}
     */
    public function activateUser($uid)
    {
        return Pi::api('user', 'uclient')->activateUser($uid);
    }

    /**
     * {@inheritDoc}
     */
    public function enableUser($uid)
    {
        return Pi::api('user', 'uclient')->enableUser($uid);
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
        return Pi::api('user', 'uclient')->get(
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
        return Pi::api('user', 'uclient')->mget(
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
        return Pi::api('user', 'uclient')->set($uid, $field, $value);
    }
    /**#@-*/

    /**
     * {@inheritDoc}
     */
    public function setRole($uid, $role)
    {
        return Pi::api('user', 'uclient')->setRole($uid, $role);
    }

    /**
     * {@inheritDoc}
     */
    public function revokeRole($uid, $role)
    {
        return Pi::api('user', 'uclient')->revokeRole($uid, $role);
    }

    /**
     * {@inheritDoc}
     */
    public function getRole($uid, $section = '')
    {
        return Pi::api('user', 'uclient')->getRole($uid, $section);
    }

    /**#@+
     * Utility APIs
     */
    /**
     * {@inheritDoc}
     */
    public function getRoute()
    {
        return parent::getRoute();
    }

    /**
     * {@inheritDoc}
     */
    public function getUrl($type, $var = null)
    {
        //return parent::getUrl($type, $var);

        $url = Pi::api('user', 'uclient')->getUrl($type, $var);

        return $url;
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
        $model = Pi::api('user', 'uclient')->getUser($uid, $field);

        return $model;
    }

    /**
     * {@inheritDoc}
     */
    public function getResource($name, $args = array())
    {
        if (!isset($this->resource[$name])) {
            $resource = Pi::api('user', 'uclient')->getResource($name);
            $this->resource[$name] = $resource;
        }
        $result = parent::getResource($name, $args);

        return $result;
    }
}
