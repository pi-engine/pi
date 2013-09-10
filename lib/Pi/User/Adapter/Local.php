<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\User\Adapter;

use Pi;
use Pi\User\Model\Local as UserModel;

/**
 * Pi Engine local user service provided by user module
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Local extends System
{
    /** @var string Route for user URLs */
    protected $route = 'user';

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
    public function getCount($condition = array())
    {
        $result = Pi::api('user', 'user')->getCount($condition);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function addUser($data)
    {
        return Pi::api('user', 'user')->addUser($data);
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
        return Pi::api('user', 'user')->disableUser($uid);
    }
    /**#@-*/

    /**#@+
     * User account/Profile fields operations
     */
    /**
     * {@inheritDoc}
     */
    public function get($uid, $field, $filter = false)
    {
        return Pi::api('user', 'user')->get($uid, $field, $filter);
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
    public function setRole($uid, $role, $section = '')
    {
        return Pi::api('user', 'user')->setRole($uid, $role, $section);
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
        return parent::getRoute();
    }

    /**
     * {@inheritDoc}
     */
    public function getUrl($type, $var = null)
    {
        return parent::getUrl($type, $var);
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate($identity, $credential)
    {
        return parent::authenticate($identity, $credential);
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
    protected function getUserModel($uid, $field = 'id')
    {
        $model = new UserModel($uid, $field);

        return $model;
    }
}
