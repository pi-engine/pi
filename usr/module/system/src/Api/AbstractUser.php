<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Api;

use Pi;
use Pi\Application\Api\AbstractApi;
use Pi\Db\Sql\Where;
use Pi\User\Model\AbstractModel as UserModel;

/**
 * Abstract User account manipulation APIs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractUser extends AbstractApi
{
    /** @var string Route for user URLs */
    protected $route = 'sysuser';

    /**
     * Get fields specs of specific type and action
     *
     * - Available types: `account`, `profile`, `compound`
     * - Available actions: `display`, `edit`, `search`
     *
     * @param string $type
     * @param string $action
     * @return array
     * @api
     */
    abstract public function getMeta($type = '', $action = '');

    /**
     * Get user model
     *
     * @param int|string $uid
     * @param string    $field
     *
     * @return UserModel|null
     */
    abstract public function getUser($uid, $field = 'id');

    /**
     * Get user IDs subject to conditions
     *
     * @param array|Where   $condition
     * @param int           $limit
     * @param int           $offset
     * @param string|array  $order
     * @return int[]
     * @api
     */
    abstract public function getUids(
        $condition  = array(),
        $limit      = 0,
        $offset     = 0,
        $order      = ''
    );

    /**
     * Get users subject to conditions
     *
     * @param array|Where   $condition
     * @param int           $limit
     * @param int           $offset
     * @param string|array  $order
     * @param array         $field
     *
     * @return array
     * @api
     */
    abstract public function getList(
        $condition  = array(),
        $limit      = 0,
        $offset     = 0,
        $order      = '',
        $field      = array()
    );

    /**
     * Get user count subject to conditions
     *
     * @param array|Where  $condition
     *
     * @return int
     * @api
     */
    abstract public function getCount($condition = array());

    /**
     * Add a user with full set of data
     *
     * @param   array   $data
     * @param   bool    $setRole
     *
     * @return  int
     * @api
     */
    public function addUser($data, $setRole = true)
    {
        $uid = (int) $this->addAccount($data);
        if ($uid && $setRole) {
            $this->setRole($uid, 'member', 'front');
        }

        return $uid;
    }

    /**
     * Update a user
     *
     * @param   int         $uid
     * @param   array       $data
     *
     * @return  bool
     * @api
     */
    public function updateUser($uid, array $data)
    {
        $uid = (int) $uid;
        if (!$uid) {
            return false;
        }

        $result = $this->updateAccount($uid, $data);

        return $result;
    }

    /**
     * Delete a user
     *
     * @param   int         $uid
     * @return  bool|null   Null for no-action
     * @api
     */
    public function deleteUser($uid)
    {
        $uid = (int) $uid;
        if (!$uid) {
            return false;
        }

        $status = $this->deleteAccount($uid);

        return $status;
    }

    /**
     * Activate a user account
     *
     * @param   int         $uid
     * @return  bool|null   Null for no-action
     * @api
     */
    public function activateUser($uid)
    {
        $uid = (int) $uid;
        if (!$uid) {
            return false;
        }

        $status = $this->activateAccount($uid);

        return $status;
    }

    /**
     * Enable a user
     *
     * @param   int     $uid
     *
     * @return  bool|null   Null for no-action
     * @api
     */
    public function enableUser($uid)
    {
        $uid = (int) $uid;
        if (!$uid) {
            return false;
        }

        $status = $this->enableAccount($uid);

        return $status;
    }

    /**
     * Disable a user
     *
     * @param   int     $uid
     *
     * @return  bool|null   Null for no-action
     * @api
     */
    public function disableUser($uid)
    {
        $uid = (int) $uid;
        if (!$uid) {
            return false;
        }

        $status = $this->enableAccount($uid, false);

        return $status;
    }

    /**
     * Get field value(s) of a user field(s)
     *
     * @param int|int[]         $uid
     * @param string|string[]   $field
     * @param bool              $filter
     * @return mixed|mixed[]
     * @api
     */
    abstract public function get($uid, $field, $filter = false);

    /**
     * Set value of a user field
     *
     * @param int       $uid
     * @param string    $field
     * @param mixed     $value
     * @return bool
     * @api
     */
    abstract public function set($uid, $field, $value);

    /**
     * Set user role(s)
     *
     * @param int           $uid
     * @param string|array  $role
     *
     * @return bool
     */
    public function setRole($uid, $role)
    {
        $uid = (int) $uid;
        if (!$uid) {
            return false;
        }

        $role = (array) $role;
        $roles = Pi::registry('role')->read();
        $roleExist = array();
        $model = Pi::model('user_role');
        $rowset = $model->select(array('uid' => $uid));
        foreach ($rowset as $row) {
            $roleExist[] = $row['role'];
        }
        $roleNew = array_intersect(
            array_diff($role, $roleExist),
            array_keys($roles)
        );
        foreach ($roleNew as $roleName) {
            $row = $model->createRow(array(
                'uid'       => $uid,
                'section'   => $roles[$roleName]['section'],
                'role'      => $roleName,
            ));
            try {
                $row->save();
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * Revoke user role(s)
     *
     * @param int          $uid
     * @param string|array $role
     *
     * @return bool
     */
    public function revokeRole($uid, $role)
    {
        $uid = (int) $uid;
        if (!$uid) {
            return false;
        }

        $role = (array) $role;
        $roles = array();
        foreach ($role as $roleName) {
            if ('member' != $roleName) {
                $roles[] = $roleName;
            }
        }
        if ($roles) {
            Pi::model('user_role')->delete(array(
                'uid'   => $uid,
                'role'  => $roles,
            ));
        }

        return true;
    }

    /**
     * Get user role
     *
     * @param int       $uid
     * @param string    $section   Section name: admin, front
     *
     * @return string[]
     */
    public function getRole($uid, $section = '')
    {
        $uid = (int) $uid;
        $section = $section ?: Pi::engine()->application()->getSection();
        if (!$uid) {
            if ('front' == $section) {
                $result = array('guest');
            } else {
                $result = array();
            }

            return $result;
        }

        $where = array(
            'uid'       => $uid,
            'section'   => $section,
        );
        $rowset = Pi::model('user_role')->select($where);
        $result = array();
        foreach ($rowset as $row) {
            $result[] = $row['role'];
        }

        return $result;
    }

    /**
     * Canonize profile field list to group by types
     *
     * @param string[] $fields
     *
     * @return array
     */
    abstract public function canonizeField(array $fields);

    /**
     * Canonize user full set data or for a specific type
     *
     * @param array     $rawData
     * @param string    $type
     * @return array
     */
    abstract public function canonizeUser(array $rawData, $type = '');

    /**
     * Add account data and generate uid, set `time_created`
     *
     * @param array $data
     *
     * @return int
     */
    public function addAccount(array $data)
    {
        $type = 'account';
        $data = $this->canonizeUser($data, $type);
        if (!isset($data['time_created'])) {
            $data['time_created'] = time();
        }
        $row = Pi::model('user_account')->createRow($data);
        $row->prepare();
        try {
            $row->save();
        } catch (\Exception $e) {
            return false;
        }

        return (int) $row['id'];
    }

    /**
     * Update user account data
     *
     * @param int $uid
     * @param array $data
     *
     * @return bool
     */
    public function updateAccount($uid, array $data)
    {
        $uid = (int) $uid;
        if (!$uid) {
            return false;
        }

        $type = 'account';
        $data = $this->canonizeUser($data, $type);
        $row = Pi::model('user_account')->find($uid);
        if ($row) {
            $row->assign($data);
            if (isset($data['credential'])) {
                $row->prepare();
            }
            try {
                $row->save();
                $status = true;
            } catch (\Exception $e) {
                $status = false;
            }
        } else {
            $status = false;
        }

        return $status;
    }

    /**
     * Delete an account and set `active` to false and set `time_deleted`
     *
     * The action is only allowed to perform once
     *
     * @param int $uid
     *
     * @return bool|null   Null for no-action
     */
    public function deleteAccount($uid)
    {
        $uid = (int) $uid;
        if (!$uid) {
            return false;
        }
        if (Pi::service('user')->isRoot($uid)) {
            return false;
        }

        $model = Pi::model('user_account');
        $row = $model->find($uid);
        if (!$row) {
            return false;
        }
        if ((int) $row['time_deleted'] > 0) {
            return null;
        }
        $row->assign(array(
            'active'        => 0,
            'time_deleted'  => time(),
        ));
        try {
            $row->save();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Activate an account and set `time_activated`
     *
     * Only non-activated and not deleted user can be activated;
     * an account is not allowed to deactivate.
     *
     * @param int $uid
     *
     * @return bool|null   Null for no-action
     */
    public function activateAccount($uid)
    {
        $uid = (int) $uid;
        if (!$uid) {
            return false;
        }

        $model = Pi::model('user_account');
        $row = $model->find($uid);
        // Skip if account not found or deleted
        if (!$row || (int) $row['time_deleted'] > 0) {
            return false;
        }
        // Skip is already activated
        if ((int) $row['time_activated'] > 0) {
            return null;
        }
        // Set active to true if activated and enabled
        if ((int) $row['time_disabled'] > 0) {
            $active = 0;
        } else {
            $active = 1;
        }
        $row->assign(array(
            'active'            => $active,
            'time_activated'    => time(),
        ));
        try {
            $row->save();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Enable/disable an account and set `time_disabled` and `active`
     *
     * Deleted accounts are not allowed to enable/disable.
     *
     * Only disabled account can be enabled, set `active` to true
     * and reset `time_disabled`; only enabled account can be disabled,
     * set `active` to false and set `time_disabled`.
     *
     * @param int   $uid
     * @param bool  $flag
     *
     * @return bool|null   Null for no-action
     */
    public function enableAccount($uid, $flag = true)
    {
        $uid = (int) $uid;
        if (!$uid) {
            return false;
        }
        if (!$flag && Pi::service('user')->isRoot($uid)) {
            return false;
        }

        $model = Pi::model('user_account');
        $row = $model->find($uid);
        // Skip if account not found or deleted
        if (!$row
            //|| (int) $row['time_activated'] < 1
            || (int) $row['time_deleted'] > 0
        ) {
            return false;
        }
        // Skip enabling if already enabled
        // Skip disabling if already disabled
        if (($flag && (int) $row['time_disabled'] == 0)
            || (!$flag && (int) $row['time_disabled'] > 0)
        ) {
            return null;
        }
        // Set active to true if activated and enabled
        if ((int) $row['time_activated'] > 0 && $flag) {
            $active = 1;
        } else {
            $active = 0;
        }
        $time = $flag ? 0 : time();
        $data = array(
            'active'            => $active,
            'time_disabled'     => $time,
        );
        $row->assign($data);
        try {
            $row->save();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Get route for URL assembling
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Get user URL
     *
     * - home: URI to user home (timeline) page
     * - profile: URI to user profile page
     * - login: URI to user login page
     * - logout: URI to user logout page
     * - register: URI to user register page
     *
     * @param string    $type URL type
     * @param mixed     $var User id for profile or redirect for login
     *
     * @return string
     * @see http://httpd.apache.org/docs/2.2/mod/core.html#allowencodedslashes
     * @api
     */
    public function getUrl($type, $var = null)
    {
        $route      = $this->getRoute();
        //$redirect   = '';
        switch ($type) {
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
                $options = array();
                if (is_string($var)) {
                    $params = array(
                        'redirect' => $var,
                    );
                } else {
                    $params = (array) $var;
                }
                if (isset($params['redirect'])) {
                    $options['query']['redirect'] = $params['redirect'];
                    unset($params['redirect']);
                } else {
                    $options['query']['redirect'] = Pi::service('url')->getRequestUri();
                }
                if (!isset($params['module'])) {
                    $params['module'] = 'system';
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

                $url = Pi::service('url')->assemble($route, $params, $options);
                break;

            case 'logout':
                $options = array();
                if (is_string($var)) {
                    $params = array(
                        'redirect' => $var,
                    );
                } else {
                    $params = (array) $var;
                }
                if (isset($params['redirect'])) {
                    $options['query']['redirect'] = $params['redirect'];
                    unset($params['redirect']);
                }
                if (!isset($params['module'])) {
                    $params['module'] = 'system';
                }
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
                $url = Pi::service('url')->assemble($route, $params, $options);
                break;

            case 'register':
                $options = array();
                if (is_string($var)) {
                    $params = array(
                        'redirect' => $var,
                    );
                } else {
                    $params = (array) $var;
                }
                if (isset($params['redirect'])) {
                    $options['query']['redirect'] = $params['redirect'];
                    unset($params['redirect']);
                } else {
                    $options['query']['redirect'] = Pi::service('url')->getRequestUri();
                }
                if (!isset($params['controller'])) {
                    $params['controller'] = 'register';
                }
                if (isset($params['route'])) {
                    $route = $params['route'];
                    unset($params['route']);
                }
                $url = Pi::service('url')->assemble($route, $params, $options);
                break;

            case 'password':
                $params = (array) $var;
                if (!isset($params['controller'])) {
                    $params['controller'] = 'password';
                }
                if (!isset($params['action'])) {
                    $params['action'] = 'find';
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

        // Append redirect with query
        // @see http://httpd.apache.org/docs/2.2/mod/core.html#allowencodedslashes
        /*
        if ($redirect) {
            if (false == strpos($url, '?')) {
                $url .= '?redirect=' . rawurlencode($redirect);
            } else {
                $url .= '&redirect=' . rawurlencode($redirect);
            }
        }
        */

        return $url;
    }
}
