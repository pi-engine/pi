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
use Pi\User\Model\System as SystemModel;

/**
 * Pi Engine built-in user service provided by system module
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class System extends AbstractAdapter
{
    /**#@+
     * Meta operations
     */
    /**
     * {@inheritDoc}
     */
    public function getMeta($type = '', $action = '')
    {
        $metaAccount = array(
            'id',
            'identity',
            'credential',
            'salt',
            'email',
            'name',
            'active',
        );
        $metaProfile = array(
            'uid',
        );
        $metaCustom = array(
        );

        $meta = array();
        switch ($type) {
            case 'account':
                $meta = $metaAccount;
                break;
            case 'profile':
                $meta = $metaProfile;
                break;
            case 'custom':
                $meta = $metaCustom;
                break;
            default:
                $meta = $metaAccount + $metaProfile + $metaCustom;
                break;
        }

        return $meta;
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
            $model = new SystemModel($uid, $field);
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
        trigger_error(__METHOD__ . ' not implemented yet', E_USER_NOTICE);
        return;
    }

    /**
     * {@inheritDoc}
     */
    public function getCount($condition = array())
    {
        trigger_error(__METHOD__ . ' not implemented yet', E_USER_NOTICE);
        return;
    }

    /**
     * {@inheritDoc}
     */
    public function addUser($data)
    {
        trigger_error(__METHOD__ . ' not implemented yet', E_USER_NOTICE);
        return;
    }

    /**
     * {@inheritDoc}
     */
    public function updateUser($uid, $data)
    {
        trigger_error(__METHOD__ . ' not implemented yet', E_USER_NOTICE);
        return;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteUser($uid)
    {
        trigger_error(__METHOD__ . ' not implemented yet', E_USER_NOTICE);
        return;
    }

    /**
     * {@inheritDoc}
     */
    public function activateUser($uid)
    {
        trigger_error(__METHOD__ . ' not implemented yet', E_USER_NOTICE);
        return;
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
        trigger_error(__METHOD__ . ' not implemented yet', E_USER_NOTICE);
        return;
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
        trigger_error(__METHOD__ . ' not implemented yet', E_USER_NOTICE);
        return;
    }

    /**
     * {@inheritDoc}
     */
    public function set($uid, $field, $value)
    {
        trigger_error(__METHOD__ . ' not implemented yet', E_USER_NOTICE);
        return;
    }

    /**
     * {@inheritDoc}
     */
    public function increment($uid, $field, $value)
    {
        trigger_error(__METHOD__ . ' not implemented yet', E_USER_NOTICE);
        return;
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
                $uid = $this->verifyUid($uid);
                $url = Pi::service('url')->assemble('sysuser', array(
                    'controller'    => 'account',
                    'id'            => $uid,
                ));
                break;
            case 'profile':
                $url = Pi::service('url')->assemble('sysuser', array(
                    'controller'    => 'profile',
                    'id'            => $uid,
                ));
                break;
            case 'login':
            case 'signin':
                $url = Pi::service('url')->assemble('sysuser', array(
                    'controller'    => 'login'
                ));
                break;
            case 'logout':
            case 'signout':
                $url = Pi::service('url')->assemble('sysuser', array(
                    'controller'    => 'login',
                    'action'        => 'logout',
                ));
                break;
            case 'register':
            case 'signup':
                $url = Pi::service('url')->assemble('sysuser', array(
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
