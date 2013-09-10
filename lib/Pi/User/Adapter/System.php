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
        $result = Pi::api('system', 'user')->getUids(
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
        $result = Pi::api('system', 'user')->getCount($condition);

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function addUser($data)
    {
        return Pi::api('system', 'user')->addUser($data);
    }

    /**
     * {@inheritDoc}
     */
    public function updateUser($uid, $data)
    {
        return Pi::api('system', 'user')->updateUser($uid, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteUser($uid)
    {
        return Pi::api('system', 'user')->deleteUser($uid);
    }

    /**
     * {@inheritDoc}
     */
    public function activateUser($uid)
    {
        return Pi::api('system', 'user')->activateUser($uid);
    }

    /**
     * {@inheritDoc}
     */
    public function enableUser($uid)
    {
        return Pi::api('system', 'user')->enableUser($uid);
    }

    /**
     * {@inheritDoc}
     */
    public function disableUser($uid)
    {
        return Pi::api('system', 'user')->disableUser($uid);
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
        return Pi::api('system', 'user')->get($uid, $field, $filter);
    }

    /**
     * {@inheritDoc}
     */
    public function set($uid, $field, $value)
    {
        return Pi::api('system', 'user')->set($uid, $field, $value);
    }
    /**#@-*/

    /**
     * {@inheritDoc}
     */
    public function setRole($uid, $role, $section = '')
    {
        return Pi::api('system', 'user')->setRole($uid, $role, $section);
    }

    /**
     * {@inheritDoc}
     */
    public function getRole($uid, $section = '')
    {
        return Pi::api('system', 'user')->getRole($uid, $section);
    }

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
    public function getUrl($type, $var = null)
    {
        $route = $this->getRoute();
        switch ($type) {
            case 'account':
            case 'profile':
                $params = array('controller' => 'profile');
                if (is_numeric($var)) {
                    $params['id'] = (int) $var;
                } else {
                    $params['identity'] = $var;
                }
                $url = Pi::service('url')->assemble($route, $params);
                break;
            case 'login':
            case 'signin':
                $params = array('controller' => 'login');
                if ($var) {
                    $params['redirect'] = $var;
                }
                $url = Pi::service('url')->assemble($route, $params);
                break;
            case 'logout':
            case 'signout':
                $params = array(
                    'controller'    => 'login',
                    'action'        => 'logout',
                );
                if ($var) {
                    $params['redirect'] = $var;
                }
                $url = Pi::service('url')->assemble($route, $params);
                break;
            case 'register':
            case 'signup':
                $url = Pi::service('url')->assemble($route, array(
                    'controller'    => 'register',
                ));
                break;
            default:
            case 'home':
                $params = array('controller' => 'home');
                if (is_numeric($var)) {
                    $params['id'] = (int) $var;
                } else {
                    $params['identity'] = $var;
                }
                $url = Pi::service('url')->assemble($route, $params);
                break;
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

    /**
     * {@inheritDoc}
     */
    public function killUser($uid)
    {
        $result = Pi::service('session')->killUser($uid);

        return $result;
    }

    /**#@-*/

    /**
     * Get user data model
     *
     * @param int|string    $uid
     * @param string        $field
     *
     * @return UserModel
     */
    protected function getUserModel($uid, $field = 'id')
    {
        $model = new UserModel($uid, $field);

        return $model;
    }
}
