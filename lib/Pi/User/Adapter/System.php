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
    /**#@+
     * Meta operations
     */
    /**
     * {@inheritDoc}
     */
    public function getMeta($type = 'account')
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
        $meta = array();
        switch ($type) {
            case 'account':
                $meta = $metaAccount;
                break;
            case 'profile':
                $meta = $metaProfile;
                break;
            default:
                $meta = $metaAccount + $metaProfile;
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
    public function getUser($id = null, $field = 'id')
    {
        if (null !== $id) {
            $model = new UserModel($id, $field);
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
    public function getIds($condition = array(),
        $limit = 0, $offset = 0, $order = '')
    {
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
        trigger_error(__METHOD__ . ' not implemented yet', E_USER_NOTICE);
    }

    /**
     * {@inheritDoc}
     */
    public function updateUser($data, $id = null)
    {
        trigger_error(__METHOD__ . ' not implemented yet', E_USER_NOTICE);
    }

    /**
     * {@inheritDoc}
     */
    public function deleteUser($id)
    {
        trigger_error(__METHOD__ . ' not implemented yet', E_USER_NOTICE);
    }

    /**
     * {@inheritDoc}
     */
    public function activateUser($id)
    {
        trigger_error(__METHOD__ . ' not implemented yet', E_USER_NOTICE);
    }

    /**
     * {@inheritDoc}
     */
    public function deactivateUser($id)
    {
        trigger_error(__METHOD__ . ' not implemented yet', E_USER_NOTICE);
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
        trigger_error(__METHOD__ . ' not implemented yet', E_USER_NOTICE);
    }

    /**
     * {@inheritDoc}
     */
    public function getList($key, $ids)
    {
        trigger_error(__METHOD__ . ' not implemented yet', E_USER_NOTICE);
    }

    /**
     * {@inheritDoc}
     */
    public function set($key, $value, $id = null)
    {
        trigger_error(__METHOD__ . ' not implemented yet', E_USER_NOTICE);
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
                $url = Pi::service('url')->assemble('user', array(
                    'controller'    => 'account',
                    'id'            => $id,
                ));
                break;
            case 'profile':
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
        trigger_error(sprintf(__CLASS__ . '::%s is not defined yet.', $method),
            E_USER_NOTICE);
    }
}
