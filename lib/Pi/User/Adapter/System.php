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
        if ($this->model
            && (null === $uid || $this->model->get($field) == $uid)
        ) {
            $model = $this->model;
        } else {
            $model = $this->getUserModel($uid, $field);
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
    public function addUser($data, $setRole = true)
    {
        return Pi::api('system', 'user')->addUser($data, $setRole);
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
        if ($this->isRoot($uid)) {
            return false;
        }
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
        if ($this->isRoot($uid)) {
            return false;
        }
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
    public function setRole($uid, $role)
    {
        return Pi::api('system', 'user')->setRole($uid, $role);
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
     *
     * @see http://httpd.apache.org/docs/2.2/mod/core.html#allowencodedslashes
     */
    public function getUrl($type, $var = null)
    {
        $route = $this->getRoute();
        switch ($type) {
            case 'account':
            case 'profile':
                $params = array();
                if (is_numeric($var)) {
                    $params['id'] = (int) $var;
                } elseif (is_string($var)) {
                    $params['name'] = $var;
                } else {
                    $params = (array) $var;
                }
                if (!isset($params['controller'])) {
                    $params['controller'] = 'profile';
                }
                if (isset($params['route'])) {
                    $route = $params['route'];
                    unset($params['route']);
                }
                $url = Pi::service('url')->assemble($route, $params);
                break;

            case 'login':
            case 'signin':
                if (is_string($var)) {
                    $params = array(
                        'redirect' => $var,
                    );
                } else {
                    $params = (array) $var;
                }
                if (isset($params['redirect'])) {
                    $redirect = $params['redirect'];
                    unset($params['redirect']);
                } else {
                    $redirect = Pi::engine()->application()->getRequest()
                        ->getRequestUri();
                }
                if (!isset($params['controller'])) {
                    $params['controller'] = 'login';
                }
                if (isset($params['route'])) {
                    $route = $params['route'];
                    unset($params['route']);
                }
                if (isset($params['section'])) {
                    $section = $params['section'];
                    unset($params['section']);
                } else {
                    $section = Pi::engine()->application()->getSection();
                }
                if ('admin' == $section) {
                    $route = 'admin';
                }
                $url = Pi::service('url')->assemble($route, $params);
                if ($redirect) {
                    $url .= '?redirect=' . rawurlencode($redirect);
                }
                break;

            case 'logout':
            case 'signout':
                if (is_string($var)) {
                    $params = array(
                        'redirect' => $var,
                    );
                } else {
                    $params = (array) $var;
                }
                if (isset($params['redirect'])) {
                    $redirect = $params['redirect'];
                    unset($params['redirect']);
                } else {
                    /*
                    $redirect = Pi::engine()->application()->getRequest()
                        ->getRequestUri();
                    */
                    $redirect = '';
                }
                $params['module'] = 'system';
                if (!isset($params['controller'])) {
                    $params['controller'] = 'login';
                }
                if (!isset($params['action'])) {
                    $params['action'] = 'logout';
                }
                if (isset($params['route'])) {
                    $route = $params['route'];
                    unset($params['route']);
                }
                if (isset($params['section'])) {
                    $section = $params['section'];
                    unset($params['section']);
                } else {
                    $section = Pi::engine()->application()->getSection();
                }
                if ('admin' == $section) {
                    $route = 'admin';
                }
                $url = Pi::service('url')->assemble($route, $params);
                if ($redirect) {
                    $url .= '?redirect=' . rawurlencode($redirect);
                }
                break;

            case 'register':
            case 'signup':
                $params = (array) $var;
                if (!isset($params['controller'])) {
                    $params['controller'] = 'register';
                }
                if (isset($params['route'])) {
                    $route = $params['route'];
                    unset($params['route']);
                }
                $url = Pi::service('url')->assemble($route, $params);
                break;

            default:
            case 'home':
                $params = array();
                if (is_numeric($var)) {
                    $params['id'] = (int) $var;
                } elseif (is_string($var)) {
                    $params['name'] = $var;
                } else {
                    $params = (array) $var;
                }
                if (!isset($params['controller'])) {
                    $params['controller'] = 'home';
                }
                if (isset($params['route'])) {
                    $route = $params['route'];
                    unset($params['route']);
                }
                $url = Pi::service('url')->assemble($route, $params);
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
     * {@inheritDoc}
     */
    public function getUserModel($uid, $field = 'id')
    {
        $model = new UserModel($uid, $field);

        return $model;
    }
}
