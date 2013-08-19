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
        if (null !== $uid) {
            $model = new LocalModel($uid, $field);
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
    public function updateUser($data, $uid = null)
    {
        return Pi::api('user', 'user')->updateUser(
            $data,
            $this->verifyUid($uid)
        );
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
    public function get($key, $uid = null)
    {
        $this->verifyId($uid);
        return Pi::api('user', 'user')->get($key, $this->verifyUid($uid));
    }

    /**
     * {@inheritDoc}
     */
    public function getList($key, $uids)
    {
        return Pi::api('user', 'user')->getList($key, $uids);
    }

    /**
     * {@inheritDoc}
     */
    public function set($key, $value, $uid = null)
    {
        return Pi::api('user', 'user')->set(
            $key,
            $value,
            $this->verifyUid($uid)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function increment($key, $value, $uid = null)
    {
        return Pi::api('user', 'user')->increment(
            $key,
            $value,
            $this->verifyUid($uid)
        );
    }
    /**#@-*/

    /**#@+
     * Utility APIs
     */
    /**
     * {@inheritDoc}
     */
    public function getUrl($type, $uid = null)
    {
        switch ($type) {
            case 'account':
            case 'profile':
            $uid = $this->verifyUid($uid);
                $url = Pi::service('url')->assemble('user', array(
                    'controller'    => 'profile',
                    'id'            => $uid,
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
