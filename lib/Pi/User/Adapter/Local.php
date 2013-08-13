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
use Pi\User\Model\Local as LocalModel;

/**
 * Pi Engine local user service provided by user module
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Local extends AbstractAdapter
{
    /**#@+
     * Meta operations
     */
    /**
     * {@inheritDoc}
     */
    public function getMeta($type = '', $action = '')
    {
        return Pi::api('user', 'profile')->getMeta($type, $action);
    }
    /**#@-*/

    /**#@+
     * User operations
     */
    /**
     * {@inheritDoc}
     */
    public function getUser($id = null, $field = 'id')
    {
        if (null !== $id) {
            $model = new LocalModel($id, $field);
        } else {
            $model = $this->model;
        }

        return $model;
    }

    /**
     * {@inheritDoc}
     */
    public function getUserList($ids)
    {
        trigger_error(__METHOD__ . ' not implemented yet', E_USER_NOTICE);
    }

    /**
     * {@inheritDoc}
     */
    public function getIds(
        $condition  = array(),
        $limit      = 0,
        $offset     = 0,
        $order      = ''
    ) {
        trigger_error(__METHOD__ . ' not implemented yet', E_USER_NOTICE);
    }

    /**
     * {@inheritDoc}
     */
    public function getCount($condition = array())
    {
        trigger_error(__METHOD__ . ' not implemented yet', E_USER_NOTICE);
    }

    /**
     * {@inheritDoc}
     */
    public function addUser($data)
    {
        return Pi::api('user', 'profile')->addUser($data);
    }

    /**
     * {@inheritDoc}
     */
    public function updateUser($data, $id = null)
    {
        $this->verifyId($id);
        return Pi::api('user', 'user')->updateUser($data, $id);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteUser($id)
    {
        return Pi::api('user', 'user')->deleteUser($id);
    }

    /**
     * {@inheritDoc}
     */
    public function activateUser($id)
    {
        return Pi::api('user', 'user')->activateUser($id);
    }

    /**
     * {@inheritDoc}
     */
    public function deactivateUser($id)
    {
        return Pi::api('user', 'user')->deactivateUser($id);
    }
    /**#@-*/

    /**#@+
     * User account/Profile fields operations
     */
    /**
     * {@inheritDoc}
     */
    public function get($key, $id = null)
    {
        $this->verifyId($id);
        return Pi::api('user', 'user')->get($key, $id);
    }

    /**
     * {@inheritDoc}
     */
    public function getList($key, $ids)
    {
        return Pi::api('user', 'profile')->getList($key, $ids);
    }

    /**
     * {@inheritDoc}
     */
    public function set($key, $value, $id = null)
    {
        $this->verifyId($id);
        return Pi::api('user', 'profile')->set($key, $value, $id);
    }

    /**
     * {@inheritDoc}
     */
    public function increment($key, $value, $id = null)
    {
        trigger_error(__METHOD__ . ' not implemented yet', E_USER_NOTICE);
    }

    /**
     * {@inheritDoc}
     */
    public function setPassword($value, $id = null)
    {
        trigger_error(__METHOD__ . ' not implemented yet', E_USER_NOTICE);
    }
    /**#@-*/

    /**#@+
     * Utility APIs
     */
    /**
     * {@inheritDoc}
     */
    public function getUrl($type, $id = null)
    {
        switch ($type) {
            case 'account':
            case 'profile':
                $id = $id ?: $this->id;
                $url = Pi::service('url')->assemble('user', array(
                    'controller'    => 'profile',
                    'id'            => $id,
                ));
                break;
            case 'login':
            case 'signin':
                $url = Pi::service('url')->assemble('user', array(
                    'controller'    => 'login'
                ));
                break;
            case 'logout':
            case 'signout':
                $url = Pi::service('url')->assemble('user', array(
                    'controller'    => 'login',
                    'action'        => 'logout',
                ));
                break;
            case 'register':
            case 'signup':
                $url = Pi::service('url')->assemble('user', array(
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
        trigger_error(__METHOD__ . ' not implemented yet', E_USER_NOTICE);
    }
    /**#@-*/


    /**
     * Method handler allows a shortcut
     *
     * @param  string  $method
     * @param  array  $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        trigger_error(
            sprintf(__CLASS__ . '::%s is not defined yet.', $method),
            E_USER_NOTICE
        );
    }
}
