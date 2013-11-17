<?php
/**
* Pi Engine (http://pialog.org)
*
* @link            http://code.pialog.org for the Pi Engine source repository
* @copyright       Copyright (c) Pi Engine http://pialog.org
* @license         http://pialog.org/license.txt New BSD License
*/

namespace Module\User\Controller\Admin;

use Module\User\Form\SearchForm;
use Pi;
use Pi\Mvc\Controller\ActionController;
use Zend\Db\Sql\Predicate;


/**
* User manage cases controller
*
* @author Liu Chuang <liuchuang@eefocus.com>
*/
class IndexController extends ActionController
{
    /**
     * Default action
     * @return array|void
     */
    public function indexAction()
    {
        $this->view()->setTemplate('user');
        $this->view()->assign(array(
            'roles'  => $this->getRoles()
        ));
    }

    /**
     * All user manage list
     *
     * @return array|void
     */
    public function allAction()
    {
        $page   = (int) $this->params('p', 1);
        $limit  = Pi::service('module')->config('list_limit', 'user');
        $offset = (int) ($page -1) * $limit;

        $condition['active']        = _get('active') ?: '';
        $condition['enable']        = _get('enable') ?: '';
        $condition['front_role']    = _get('front_role') ?: '';
        $condition['admin_role']    = _get('admin_role') ?: '';
        $condition['register_date'] = _get('register_date') ?: '';
        $condition['name']          = _get('name') ?: '';
        $condition['email']         = _get('email') ?: '';

        // Get user ids
        $uids  = $this->getUids($condition, $limit, $offset);

        // Get user count
        $count = $this->getCount($condition);

        // Get user information
        $users = $this->getUser($uids);

        // Set paginator
        $paginator = array(
            'count'      => (int) $count,
            'limit'      => (int) $limit,
            'page'       => $page,
        );

        $data = array(
            'users'       => array_values($users),
            'paginator'   => $paginator,
            'condition'   => $condition,
        );

        return $data;

    }

    /**
     * Activated user list
     */
    public function activatedAction()
    {
        $page   = (int) $this->params('p', 1);
        $limit  = Pi::service('module')->config('list_limit', 'user');
        $offset = (int) ($page -1) * $limit;

        $condition['activated']     = 'activated';
        $condition['active']        = _get('active') ?: '';
        $condition['enable']        = _get('enable') ?: '';
        $condition['front_role']    = _get('front_role') ?: '';
        $condition['admin_role']    = _get('admin_role') ?: '';
        $condition['register_date'] = _get('register_date') ?: '';
        $condition['search']        = _get('search') ?: '';
        $condition['name']          = _get('name') ?: '';
        $condition['email']         = _get('email') ?: '';

        // Get user ids
        $uids  = $this->getUids($condition, $limit, $offset);

        // Get user count
        $count = $this->getCount($condition);

        // Get user information
        $users = $this->getUser($uids);

        // Set paginator
        $paginator = array(
            'count'      => (int) $count,
            'limit'      => (int) $limit,
            'page'       => $page,
        );

        $data = array(
            'users'       => array_values($users),
            'paginator'   => $paginator,
            'condition'   => $condition,
        );

        return $data;

    }

    /**
     * Pending user list
     */
    public function pendingAction()
    {
        $page   = (int) $this->params('p', 1);
        $limit  = Pi::service('module')->config('list_limit', 'user');
        $offset = (int) ($page -1) * $limit;

        $condition['activated']     = 'pending';
        $condition['enable']        = _get('enable') ?: '';
        $condition['front_role']    = _get('front_role') ?: '';
        $condition['admin_role']    = _get('admin_role') ?: '';
        $condition['register_date'] = _get('register_date') ?: '';
        $condition['search']        = _get('search') ?: '';
        $condition['name']          = _get('name') ?: '';
        $condition['email']         = _get('email') ?: '';

        // Get user ids
        $uids  = $this->getUids($condition, $limit, $offset);

        // Get user count
        $count = $this->getCount($condition);

        // Get user information
        $users = $this->getUser($uids);

        // Set paginator
        $paginator = array(
            'count'      => (int) $count,
            'limit'      => (int) $limit,
            'page'       => $page,
        );

        $data = array(
            'users'       => array_values($users),
            'paginator'   => $paginator,
            'condition'   => $condition,
        );

        return $data;

    }

    /**
     * Add new user action
     */
    public function addUserAction()
    {
        $result = array(
            'status' => 0,
            'message' => '',
        );

        $identity   = _post('identity');
        $email      = _post('email');
        $name       = _post('name');
        $credential = _post('credential');
        $activated  = (int) _post('activated');
        $enable     = (int) _post('enable');
        $roles      = _post('roles');

        $configs = Pi::service('module')->config('', 'user');
        // Check username
        if (strlen($identity) > $configs['uname_max'] ||
            strlen($identity) < $configs['uname_min']
        ) {

            $result['message'] = _a(sprintf(
                'Add user failed: username should between %s to %s',
                $configs['uname_min'],
                $configs['uname_max']
            ));

            return $result;
        }

        // Check name
        if (strlen($name) > $configs['name_max'] ||
            strlen($name) < $configs['name_min']
        ) {
            $result['message'] = _a(sprintf(
                'Add user failed: name should between %s to %s',
                $configs['name_min'],
                $configs['name_max']
            ));

            return $result;
        }

        // Check credential
        if (strlen($credential) > $configs['password_max'] ||
            strlen($credential) < $configs['password_min]']
        ) {
            $result['message'] = _a(sprintf(
                'Add user failed: password should between %s to %s',
                $minCredential,
                $maxCredential
            ));

            return $result;
        }


        // Check duplication
        $where = array(
            'identity' => $identity,
            'name'     => $name,
            'email'    => $email,
        );
        $select = Pi::model('user_account')->select()->where(
            $where,
            Predicate\PredicateSet::OP_OR
        );
        $rowset = Pi::model('user_account')->selectWith($select)->toArray();
        if (count($rowset) != 0) {
            $result['message'] = _a('Add user failed: user already exists.');
            return $result;
        }

        $data = array(
            'identity'      => $identity,
            'name'          => $name,
            'email'         => $email,
            'credential'    => $credential,
            'last_modified' => time(),
        );

        // Add user
        $uid = Pi::api('user', 'user')->addUser($data);
        if (!$uid) {
            $result['message'] = _a('Add user failed: invalid operation.');
            return $result;
        }

        // Activate
        if ($activated == 1) {
            Pi::api('user', 'user')->activateUser($uid);
        }

        // Enable
        if ($enable == 1) {
            Pi::api('user', 'user')->enableUser($uid);
        } else {
            Pi::api('user', 'user')->disableUser($uid);
        }

        // Set role
        Pi::api('user', 'user')->setRole($uid, $roles);

        $result['status']  = 1;
        $result['message'] = _a('Add user successfully');

        return $result;

    }

    /**
     * Check username, email, display name exist
     *
     * @return array
     */
    public function checkExistAction()
    {
        $status = 1;

        $identity = _get('identity');
        $email    = _get('email');
        $name     = _get('name');
        $uid      = (int) _get('id');

        if (!$identity && !$email && !$name ) {
            return array(
                'status' => $status,
            );
        }

        $model = Pi::model('user_account');
        if ($identity) {
            $row = $model->find($identity, 'identity');
            if (!$row) {
                $status = 0;
            } else {
                $status = ($row['id'] == $uid) ? 0 : 1;
            }
        }

        if ($email) {
            $row = $model->find($email, 'email');
            if (!$row) {
                $status = 0;
            } else {
                $status = ($row['id'] == $uid) ? 0 : 1;
            }
        }

        if ($name) {
            $row = $model->find($name, 'name');
            if (!$row) {
                $status = 0;
            } else {
                $status = ($row['id'] == $uid) ? 0 : 1;
            }
        }

        return array(
            'status' => $status,
        );

    }

    /**
     * Display search result
     */
    public function searchAction()
    {
        $condition['uid']               = _get('uid') ?: '';
        $condition['active']            = _get('active') ?: '';
        $condition['enable']            = _get('enable') ?: '';
        $condition['activated']         = _get('activated') ?: '';
        $condition['front_role']        = _get('front_role') ?: '';
        $condition['admin_role']        = _get('admin_role') ?: '';
        $condition['identity']          = _get('identity') ?: '';
        $condition['name']              = _get('name') ?: '';
        $condition['email']             = _get('email') ?: '';
        $condition['time_created_from'] = _get('time_created_from') ?: '';
        $condition['time_created_to']   = _get('time_created_to') ?: '';
        $condition['ip_register']       = _get('ip_register') ?: '';

        if ($condition['front_role']) {
            $condition['front_role'] = array_unique(explode(',', $condition['front_role']));
            $condition['front_role'] = array_filter($condition['front_role']);
        }
        if ($condition['admin_role']) {
            $condition['admin_role'] = array_unique(explode(',', $condition['admin_role']));
            $condition['admin_role'] = array_filter($condition['admin_role']);
        }

        $page   = (int) $this->params('p', 1);
        $limit  = Pi::service('module')->config('list_limit', 'user');
        $offset = (int) ($page -1) * $limit;

        // Get user ids
        $uids  = $this->getUids($condition, $limit, $offset);;

        // Get user count
        $count = $this->getCount($condition);

        // Get user information
        $users = $this->getUser($uids);

        // Set paginator
        $paginator = array(
            'count'      => (int) $count,
            'limit'      => (int) $limit,
            'page'       => $page,
        );

        $data = array(
            'users'       => array_values($users),
            'paginator'   => $paginator,
            'condition'   => $condition,
        );

        return $data;

    }

    /**
     * Enable users
     *
     * @return array
     */
    public function enableAction()
    {
        $result = array(
            'status'  => 0,
            'message' => '',
        );

        $uids = _post('ids', '');
        if (!$uids) {
            $result['message'] = _a('Enable user failed: invalid uid');
            return $result;
        }

        $uids  = explode(',', $uids);
        $count = 0;
        foreach ($uids as $uid) {
            $status = Pi::api('user', 'user')->enableUser($uid);
            if ($status) {
                $count++;
            }
        }

        $usersStatus = $this->getUserStatus($uids);
        $result['users_status'] = $usersStatus;
        $result['status']  = 1;
        $result['message'] = sprintf(_a('%d enable user successfully'), $count);

        return $result;

    }

    /**
     * Disable user
     *
     * @return array
     */
    public function disableAction()
    {
        $result = array(
            'status'  => 0,
            'message' => ''
        );
        $uids = _post('ids', '');

        if (!$uids) {
            $result['message'] = _a('Disable user failed: invalid uid');
            return $result;
        }

        $uids  = explode(',', $uids);
        $count = 0;
        foreach ($uids as $uid) {
            $status = Pi::api('user', 'user')->disableUser($uid);
            if ($status) {
                $count++;
            }
        }

        $usersStatus = $this->getUserStatus($uids);
        $result['users_status'] = $usersStatus;
        $result['status']  = 1;
        $result['message'] = sprintf(_a('%d disable user successfully'), $count);

        return $result;

    }

    /**
     * Delete user
     *
     * @return array
     */
    public function deleteUserAction()
    {
        $uids   = _post('ids');
        $result = array(
            'status'  => 0,
            'message' => '',
        );

        if (!$uids) {
            $result['message'] = _a('Delete user failed: invalid uid');
            return $result;
        }

        $uids  = explode(',', $uids);
        $count = 0;
        foreach ($uids as $uid) {
            $status = Pi::api('user', 'user')->deleteUser($uid);
            if (!is_array($status) && $status !== false) {
                $count++;
                // Clear user other info: user data, role, log, privacy, timeline
                $this->deleteUser($uid, 'user_data');
                $this->deleteUser($uid, 'user_role');
                $this->deleteUser($uid, 'user_log', 'user');
                $this->deleteUser($uid, 'privacy_user', 'user');
                $this->deleteUser($uid, 'timeline_log', 'user');
            }
        }

        $usersStatus = $this->getUserStatus($uids);
        $result['users_status'] = $usersStatus;
        $result['status']  = 1;
        $result['message'] = sprintf(_a('%d delete user successfully'), $count);

        return $result;

    }

    /**
     * Activate user or users
     *
     */
    public function activateUserAction()
    {
        $uids = _post('ids');

        $result = array(
            'status'  => 0,
            'message' => ''
        );

        if (!$uids) {
            $result['message'] = _a('Activate user failed: invalid uid');
            return $result;
        }

        $uids = array_unique(explode(',', $uids));
        if (empty($uids)) {
            $result['message'] = _a('Activate user failed: invalid uid');
            return $result;
        }

        $count = 0;
        foreach ($uids as $uid) {
            $status = Pi::api('user', 'user')->activateUser($uid);
            if ($status) {
                $count++;
            }
        }

        $usersStatus = $this->getUserStatus($uids);
        $result['users_status'] = $usersStatus;
        $result['status']  = 1;
        $result['message'] = sprintf(_a('%d activated user successfully'), $count);

        return $result;

    }

    /**
     * Assign role
     * Type: add, remove
     *
     * @return array
     */
    public function assignRoleAction()
    {
        $uids = _post('ids');
        $type = _post('type');
        $role = _post('role');

        $result = array(
            'status'  => 0,
            'message' => '',
        );

        $result = array(
            'status'    => 0,
            'data'      => array(),
            'message'   => '',
        );

        if (!$uids || !$type || !$role) {
            $result['message'] = _a('Assign role failed: invalid parameters.');
            return $result;
        }

        $uids = array_unique(explode(',', $uids));
        if (!$uids) {
            $result['message'] = _a('Assign role failed: invalid user ids.');
            return $result;
        }

        if (!in_array($type, array('add', 'remove'))) {
            $result['message'] = _a('Assign role failed: invalid operation.');
            return $result;
        }

        // Add user role
        if ($type == 'add') {
            foreach ($uids as $uid) {
                $status = Pi::api('user', 'user')->setRole($uid, $role);
                if (!$status) {
                    $result['message'] = _a('Assign role failed.');
                    return $result;
                }
            }
        }

        // Remove user role
        if ($type == 'remove') {
            foreach ($uids as $uid) {
                $status = Pi::api('user', 'user')->revokeRole($uid, $role);
                if (!$status) {
                    $result['message'] = _a('Assign role failed');
                    return $result;
                }
            }
        }

        $users = array();
        array_walk($uids, function ($uid) use (&$users) {
            $users[$uid] = array('id' => $uid);
        });
        $data = $this->renderRole($users);
        $result['data'] = $data;
        $result['status']  = 1;
        $result['message'] = _a('Assign role successfully');

        return $result;

    }

    /**
     * Get user information
     *
     * @param int[] $uids
     *
     * @return array
     */
    protected function getUser($uids)
    {
        $users = array();
        if (!$uids) {
            return $users;
        }

        $columns = array(
            'identity'       => '',
            'name'           => '',
            'email'          => '',
            'active'         => '',
            'time_disabled'  => '',
            'time_activated' => '',
            'time_created'   => '',
            'ip_register'    => '',
            'id'             => '',
        );

        $noSortUser = Pi::api('user', 'user')->get(
            $uids,
            array_keys($columns)
        );

        foreach ($uids as $uid) {
            $users[] = $noSortUser[$uid];
        }
        array_walk($users, function (&$user) {
            $user['link'] = Pi::service('user')->getUrl('home', array(
                'id'    => (int) $user['id'],
            ));
            $user['active']         = (int) $user['active'];
            $user['time_disabled']  = $user['time_disabled']
                ? _date($user['time_disabled']) : 0;
            $user['time_activated']  = $user['time_activated']
                ? _date($user['time_activated']) : 0;
            $user['time_created']  = $user['time_created']
                ? _date($user['time_created']) : 0;
        });
        $users = $this->renderRole($users);

        return $users;

    }

    /**
     * Get user ids according to condition
     *
     * @param $condition
     * @param $type
     * @param int $limit
     * @param int $offset
     * @return array
     *
     */
    protected function getUids($condition, $limit = 0, $offset = 0)
    {
        $modelAccount = Pi::model('user_account');
        $modelRole    = Pi::model('user_role');

        $where['time_deleted'] = 0;
        if ($condition['active'] == 'active') {
            $where['active'] = 1;
        }
        if ($condition['active'] == 'inactive') {
            $where['active'] = 0;
        }
        if ($condition['enable'] == 'enable') {
            $where['time_disabled'] = 0;
        }
        if ($condition['enable'] == 'disable') {
            $where['time_disabled > ?'] = 0;
        }
        if ($condition['activated'] == 'activated') {
            $where['time_activated > ?'] = 0;
        }
        if ($condition['activated'] == 'pending') {
            $where['time_activated'] = 0;
        }
        if ($condition['register_date']) {
            $where['time_created >= ?'] = $this->canonizeRegisterDate(
                $condition['register_date']
            );
        }
        if ($condition['uid']) {
            $where['id'] = (int) $condition['uid'];
        }
        if ($condition['email']) {
            $where['email like ?'] = '%' .$condition['email'] . '%';
        }
        if ($condition['identity']) {
            $where['identity like ?'] = '%' . $condition['identity'] . '%';
        }
        if ($condition['name']) {
            $where['name like ?'] = '%' . $condition['name'] . '%';

        }
        if ($condition['time_created_from']) {
            $where['time_created >= ?'] = strtotime($condition['time_created_from']);
        }
        if ($condition['time_created_to']) {
            $where['time_created <= ?'] = strtotime($condition['time_created_to'] . ' +1 day');
        }

        $whereAccount = Pi::db()->where()->create($where);
        $where = Pi::db()->where();
        $where->add($whereAccount);

        $select = Pi::db()->select();
        $select->from(
            array('account' => $modelAccount->getTable()),
            array('id')
        );
        if ($condition['front_role']) {
            if (is_array($condition['front_role'])) {
                $i = 1;
                foreach ($condition['front_role'] as $role) {
                    $prefix = $i;
                    $whereRoleFront = Pi::db()->where()->create(array(
                        'front' . $prefix . '.role'    => $role,
                        'front' . $prefix . '.section'  => 'front',
                    ));
                    $where->add($whereRoleFront);
                    $select->join(
                        array('front' . $prefix => $modelRole->getTable()),
                        'front' . $prefix . '.uid=account.id',
                        array()
                    );
                    $i++;
                }
            } else {
                $whereRoleFront = Pi::db()->where()->create(array(
                    'front.role'    => $condition['front_role'],
                    'front.section' => 'front',
                ));
                $where->add($whereRoleFront);
                $select->join(
                    array('front' => $modelRole->getTable()),
                    'front.uid=account.id',
                    array()
                );
            }
        }

        if ($condition['admin_role']) {
            if (is_array($condition['admin_role'])) {
                $i = 1;
                foreach ($condition['admin_role'] as $role) {
                    $prefix = $i;
                    $whereRoleFront = Pi::db()->where()->create(array(
                        'admin' . $prefix . '.role'     => $role,
                        'admin' . $prefix . '.section'  => 'admin',
                    ));
                    $where->add($whereRoleFront);
                    $select->join(
                        array('admin' . $prefix => $modelRole->getTable()),
                        'admin' . $prefix . '.uid=account.id',
                        array()
                    );
                    $i++;
                }
            } else {
                $whereRoleFront = Pi::db()->where()->create(array(
                    'admin.role'    => $condition['admin_role'],
                    'admin.section' => 'admin',
                ));
                $where->add($whereRoleFront);
                $select->join(
                    array('admin' => $modelRole->getTable()),
                    'admin.uid=account.id',
                    array()
                );
            }
        }

        if ($condition['ip_register']) {
            $profileModel = $this->getModel('profile');
            $whereProfile = Pi::db()->where()->create(array(
                'profile.ip_register like ?' => '%' . $condition['ip_register'] . '%',
            ));
            $where->add($whereProfile);
            $select->join(
                array('profile' => $profileModel->getTable()),
                'profile.uid=account.id',
                array()
            );
        }

        $select->order('account.id DESC');
        if ($limit) {
            $select->limit($limit);
        }
        if ($offset) {
            $select->offset($offset);
        }

        $select->where($where);

        $rowset = Pi::db()->query($select);

        $result = array();
        foreach ($rowset as $row) {
            $result1[] = $row;
            $result[] = (int) $row['id'];
        }

        return $result;

    }

    /**
     * Get count according to condition
     *
     * @param $condition
     * @param $type
     * @return int
     */
    protected function getCount($condition)
    {
        $modelAccount = Pi::model('user_account');
        $modelRole    = Pi::model('user_role');

        $where = array('time_deleted' => 0);
        if ($condition['active'] == 'active') {
            $where['active'] = 1;
        }
        if ($condition['active'] == 'inactive') {
            $where['active'] = 0;
        }
        if ($condition['enable'] == 'enable') {
            $where['time_disabled'] = 0;
        }
        if ($condition['enable'] == 'disable') {
            $where['time_disabled > ?'] = 0;
        }
        if ($condition['activated'] == 'activated') {
            $where['time_activated > ?'] = 0;
        }
        if ($condition['activated'] == 'pending') {
            $where['time_activated'] = 0;
        }
        if ($condition['register_date']) {
            $where['time_created >= ?'] = $this->canonizeRegisterDate(
                $condition['register_date']
            );
        }
        if ($condition['uid']) {
            $where['id'] = (int) $condition['uid'];
        }
        if ($condition['email']) {
            $where['email like ?'] = '%' .$condition['email'] . '%';
        }
        if ($condition['identity']) {
            $where['identity like ?'] = '%' . $condition['identity'] . '%';
        }
        if ($condition['name']) {
            $where['name like ?'] = '%' . $condition['name'] . '%';

        }
        if ($condition['time_created_from']) {
            $where['time_created >= ?'] = strtotime($condition['time_created_from']);
        }
        if ($condition['time_created_to']) {
            $where['time_created <= ?'] = strtotime($condition['time_created_to'] . ' +1 day');
        }

        $whereAccount = Pi::db()->where()->create($where);
        $where = Pi::db()->where();
        $where->add($whereAccount);

        $select = Pi::db()->select();
        $select->from(
            array('account' => $modelAccount->getTable())
        );

        $select->columns(array(
            'count' => Pi::db()->expression('COUNT(account.id)'),
        ));

        if ($condition['front_role']) {
            if (is_array($condition['front_role'])) {
                $i = 1;
                foreach ($condition['front_role'] as $role) {
                    $prefix = $i;
                    $whereRoleFront = Pi::db()->where()->create(array(
                        'front' . $prefix . '.role'    => $role,
                        'front' . $prefix . '.section'  => 'front',
                    ));
                    $where->add($whereRoleFront);
                    $select->join(
                        array('front' . $prefix => $modelRole->getTable()),
                        'front' . $prefix . '.uid=account.id',
                        array()
                    );
                    $i++;
                }
            } else {
                $whereRoleFront = Pi::db()->where()->create(array(
                    'front.role'    => $condition['front_role'],
                    'front.section' => 'front',
                ));
                $where->add($whereRoleFront);
                $select->join(
                    array('front' => $modelRole->getTable()),
                    'front.uid=account.id',
                    array()
                );
            }
        }

        if ($condition['admin_role']) {
            if (is_array($condition['admin_role'])) {
                $i = 1;
                foreach ($condition['admin_role'] as $role) {
                    $prefix = $i;
                    $whereRoleFront = Pi::db()->where()->create(array(
                        'admin' . $prefix . '.role'     => $role,
                        'admin' . $prefix . '.section'  => 'admin',
                    ));
                    $where->add($whereRoleFront);
                    $select->join(
                        array('admin' . $prefix => $modelRole->getTable()),
                        'admin' . $prefix . '.uid=account.id',
                        array()
                    );
                    $i++;
                }
            } else {
                $whereRoleFront = Pi::db()->where()->create(array(
                    'admin.role'    => $condition['admin_role'],
                    'admin.section' => 'admin',
                ));
                $where->add($whereRoleFront);
                $select->join(
                    array('admin' => $modelRole->getTable()),
                    'admin.uid=account.id',
                    array()
                );
            }
        }

        if ($condition['ip_register']) {
            $profileModel = $this->getModel('profile');
            $whereProfile = Pi::db()->where()->create(array(
                'profile.ip_register like ?' => '%' . $condition['ip_register'] . '%',
            ));
            $where->add($whereProfile);
            $select->join(
                array('profile' => $profileModel->getTable()),
                'profile.uid=account.id',
                array()
            );
        }

        $select->where($where);
        $rowset = Pi::db()->query($select);

        if ($rowset) {
            $rowset = $rowset->current();
        } else {
            return 0;
        }

        return (int) $rowset['count'];

    }

    /**
     * Get role list
     *
     * @return array
     */
    protected function getRoles()
    {
        $roles = Pi::registry('role')->read();
        $data  = array();
        foreach ($roles as $name => $role) {
            if ('guest' == $name) {
                continue;
            }
            $data[] = array(
                'name'  => $name,
                'title' => $role['title'],
                'type'  => $role['section'],
            );
        }

        return $data;

    }

    /**
     * Canonize register date
     *
     * @param string $registerDate
     *
     * @return int
     */
    protected function canonizeRegisterDate($registerDate)
    {
        $time = 0;
        if ($registerDate == 'today') {
            $time = mktime(
                0,0,0,
                date("m"),
                date("d"),
                date("Y")
            );
        }

        if ($registerDate == 'last_week') {
            $time = mktime(
                0,0,0,
                date("m"),
                date("d") - 7,
                date("Y")
            );
        }

        if ($registerDate == 'last_month') {
            $time = mktime(
                0,0,0,
                date("m") - 1,
                date("d"),
                date("Y")
            );
        }

        if ($registerDate == 'last_3_month') {
            $time = mktime(
                0,0,0,
                date("m") - 3,
                date("d"),
                date("Y")
            );
        }

        if ($registerDate == 'last_year') {
            $time = mktime(
                0,0,0,
                date("m"),
                date("d"),
                date("Y") - 1
            );
        }

        return $time;

    }

    /**
     * Render roles for users
     */
    protected function renderRole(array $users)
    {
        foreach ($users as $key => $user) {
            $uids[] = $user['id'];
        }
        $roleList = array();
        $roles = Pi::registry('role')->read();
        $rowset = Pi::model('user_role')->select(array('uid' => $uids));
        foreach ($rowset as $row) {
            $uid     = $row['uid'];
            $section = $row['section'];
            $roleKey = $section . '_roles';
            $roleList[$uid][$roleKey][] = $roles[$row['role']]['title'];
        }
        array_walk($users, function (&$user) use ($roleList) {
            $uid = $user['id'];
            if (isset($roleList[$uid]['front_roles'])) {
                $user['front_roles'] = $roleList[$uid]['front_roles'];
            }
            if (isset($roleList[$uid]['admin_roles'])) {
                $user['admin_roles'] = $roleList[$uid]['admin_roles'];
            }
        });

        return $users;
    }

    /**
     * Get users status: active, activated, disable
     *
     * @param $uids
     * @return array
     */
    protected function getUserStatus($uids)
    {
        $uids  = (array) $uids;
        $users = Pi::api('user', 'user')->get(
            $uids,
            array(
                'active','time_activated', 'time_disabled'
            )
        );

        $usersStatus = array();
        foreach ($users as $user) {
            $usersStatus[$user['id']] = array(
                'active'    => (int) $user['active'],
                'activated' => $user['time_activated'] ? 1 : 0,
                'disabled'  => $user['time_disabled'] ? 1 : 0,
            );
        }

        return $usersStatus;

    }

    /**
     * Delete user field
     *
     * @param $uid
     * @param $field
     * @param string $type core or user
     * @return int
     */
    protected function deleteUser($uid, $field, $type = '')
    {
        if ($type) {
            try {
                Pi::model($field, $type)->delete(array('uid' => $uid));
                $status = 1;
            } catch (\Exception $e) {
                $status = 0;
            }
        } else {
            try {
                Pi::model($field)->delete(array('uid' => $uid));
                $status = 1;
            } catch (\Exception $e) {
                $status = 0;
            }
        }

        return $status;
    }
}