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
use Pi\User\Model\System as UserModel;

/**
 * Pi Engine built-in user service provided by system module
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class System extends AbstractAdapter
{
    /** @var string Route for user URLs */
    protected $route = 'sysuser';

    /**#@+
     * Meta operations
     */
    /**
     * {@inheritDoc}
     */
    public function getMeta($type = '', $action = '')
    {
        return Pi::api('system', 'user')->getMeta($type, $action);
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
        if (null !== $uid) {
            $model = $this->getUserModel($uid, $field);
        } else {
            $model = $this->model;
        }

        return $model;
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
        $result = Pi::api('user', 'system')->getUids(
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
        $result = Pi::api('user', 'system')->getCount($condition);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function addUser($data)
    {
        return Pi::api('user', 'system')->addUser($data);
    }

    /**
     * {@inheritDoc}
     */
    public function updateUser($uid, $data)
    {
        return Pi::api('user', 'system')->updateUser($uid, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteUser($uid)
    {
        return Pi::api('user', 'system')->deleteUser($uid);
    }

    /**
     * {@inheritDoc}
     */
    public function activateUser($uid)
    {
        return Pi::api('user', 'system')->activateUser($uid);
    }

    /**
     * {@inheritDoc}
     */
    public function enableUser($uid)
    {
        return Pi::api('user', 'system')->enableUser($uid);
    }

    /**
     * {@inheritDoc}
     */
    public function disableUser($uid)
    {
        return Pi::api('user', 'system')->disableUser($uid);
    }
    /**#@-*/

    /**#@+
     * User account/Profile fields operations
     */
    /**
     * {@inheritDoc}
     */
    public function get($uid, $field, $filter = true)
    {
        return Pi::api('user', 'system')->get($uid, $field, $filter);
    }

    /**
     * {@inheritDoc}
     */
    public function set($uid, $field, $value)
    {
        return Pi::api('user', 'system')->set($uid, $field, $value);
    }

    /**
     * {@inheritDoc}
     */
    public function increment($uid, $field, $value)
    {
        return Pi::api('user', 'system')->increment($uid, $field, $value);
    }
    /**#@-*/

    /**#@+
     * Utility APIs
     */

    /**
     * {@inheritDoc}
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * {@inheritDoc}
     */
    public function getUrl($type, $uid = null)
    {
        $route = $this->getRoute();
        switch ($type) {
            case 'account':
            case 'profile':
                $url = Pi::service('url')->assemble($route, array(
                    'controller'    => 'profile',
                    'id'            => $uid,
                ));
                break;
            case 'login':
            case 'signin':
                $url = Pi::service('url')->assemble($route, array(
                    'controller'    => 'login'
                ));
                break;
            case 'logout':
            case 'signout':
                $url = Pi::service('url')->assemble($route, array(
                    'controller'    => 'login',
                    'action'        => 'logout',
                ));
                break;
            case 'register':
            case 'signup':
                $url = Pi::service('url')->assemble($route, array(
                    'controller'    => 'register',
                ));
                break;
            default:
                $url = '';
                break;
        }

        return $url;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate($identity, $credential)
    {
        $options = array();
        if (isset($this->options['authentication'])) {
            $options = $this->options['authentication'];
        }
        $service = Pi::service()->load('authentication', $options);
        $result = $service->authenticate($identity, $credential);

        return $result;
    }
    /**#@-*/

    /**
     * Get user data model
     *
     * @param int       $uid
     * @param string    $field
     *
     * @return UserModel
     */
    protected function getUserModel($uid, $field = 'id')
    {
        $model = new UserModel($uid, $field);

        return $model;
    }
}
