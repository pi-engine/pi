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

/**
 * Pi Engine Client user service provided by uclient module
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Client extends System
{
    /** @var string Route for user URLs */
    //protected $route = 'uclient';

    /**#@+
     * Meta operations
     */
    /**
     * {@inheritDoc}
     */
    public function getMeta($type = '', $action = '')
    {
        return Pi::api('uclient', 'user')->getMeta($type, $action);
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
        $result = Pi::api('uclient', 'user')->getUids(
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
        $result = Pi::api('uclient', 'user')->getList(
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
        $result = Pi::api('uclient', 'user')->getCount($condition);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function addUser($data, $setRole = true)
    {
        return Pi::api('uclient', 'user')->addUser($data, $setRole);
    }

    /**
     * {@inheritDoc}
     */
    public function updateUser($uid, $data)
    {
        return Pi::api('uclient', 'user')->updateUser($uid, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteUser($uid)
    {
        if ($this->isRoot($uid)) {
            return false;
        }
        return Pi::api('uclient', 'user')->deleteUser($uid);
    }

    /**
     * {@inheritDoc}
     */
    public function activateUser($uid)
    {
        return Pi::api('uclient', 'user')->activateUser($uid);
    }

    /**
     * {@inheritDoc}
     */
    public function enableUser($uid)
    {
        return Pi::api('uclient', 'user')->enableUser($uid);
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
    public function get($uid, $field = array(), $filter = false)
    {
        return Pi::api('uclient', 'user')->get($uid, $field, $filter);
    }
    /**
     * {@inheritDoc}
     */
    public function mget(array $uids, $field = array(), $filter = false)
    {
        return Pi::api('uclient', 'user')->mget($uids, $field, $filter);
    }

    /**
     * {@inheritDoc}
     */
    public function set($uid, $field, $value)
    {
        return Pi::api('uclient', 'user')->set($uid, $field, $value);
    }
    /**#@-*/

    /**
     * {@inheritDoc}
     */
    public function setRole($uid, $role)
    {
        return Pi::api('uclient', 'user')->setRole($uid, $role);
    }

    /**
     * {@inheritDoc}
     */
    public function revokeRole($uid, $role)
    {
        return Pi::api('uclient', 'user')->revokeRole($uid, $role);
    }

    /**
     * {@inheritDoc}
     */
    public function getRole($uid, $section = '')
    {
        return Pi::api('uclient', 'user')->getRole($uid, $section);
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

        $url = Pi::api('uclient', 'user')->getUrl($type, $var);

        return $url;
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
    public function getUserModel($uid, $field = 'id')
    {
        $model = Pi::api('uclient', 'user')->getUser($uid, $field);

        return $model;
    }

    /**
     * {@inheritDoc}
     */
    public function getResource($name, $args = array())
    {
        $resource = parent::getResource($name, $args);
        $clientConfig = Pi::api('uclient', 'user')->config();
        $config = array(
            'app_key'       => $clientConfig['app_key'],
            'authorization' => $clientConfig['authorization'],
        );
        if (!empty($clientConfig[$name]))  {
            $config = array_merge($config, $clientConfig[$name]);
        }
        $resource->setOptions($config);

        return $resource;
    }
}
