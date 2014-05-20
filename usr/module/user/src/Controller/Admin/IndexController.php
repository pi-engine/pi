<?php
/**
* Pi Engine (http://pialog.org)
*
* @link            http://code.pialog.org for the Pi Engine source repository
* @copyright       Copyright (c) Pi Engine http://pialog.org
* @license         http://pialog.org/license.txt BSD 3-Clause License
*/
namespace Module\User\Controller\Admin;

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
        $limit  = Pi::config('list_limit', 'user');
        $offset = (int) ($page -1) * $limit;

        $condition['active']        = _get('active') ?: '';
        $condition['enable']        = _get('enable') ?: '';
        $condition['front_role']    = _get('front_role') ?: '';
        $condition['admin_role']    = _get('admin_role') ?: '';
        $condition['register_date'] = _get('register_date') ?: '';
        $condition['name']          = _get('name') ?: '';
        $condition['email']         = _get('email') ?: '';

        list($users, $count) = $this->getUsers($condition, $limit, $offset);

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
        $limit  = Pi::config('list_limit', 'user');
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

        list($users, $count) = $this->getUsers($condition, $limit, $offset);

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
        $limit  = Pi::config('list_limit', 'user');
        $offset = (int) ($page -1) * $limit;

        $condition['activated']     = 'pending';
        $condition['enable']        = _get('enable') ?: '';
        $condition['front_role']    = _get('front_role') ?: '';
        $condition['admin_role']    = _get('admin_role') ?: '';
        $condition['register_date'] = _get('register_date') ?: '';
        $condition['search']        = _get('search') ?: '';
        $condition['name']          = _get('name') ?: '';
        $condition['email']         = _get('email') ?: '';

        list($users, $count) = $this->getUsers($condition, $limit, $offset);

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
            'ip_register'   => Pi::user()->getIp()
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
            $this->sendNotification($uid);
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
        $result = array(
            'status' => 1,
        );

        $query  = array();
        foreach (array('identity', 'email', 'name') as $param) {
            $val = $this->params($param);
            if ($val) {
                $query[$param] = $val;
            }
        }
        if (!$query) {
            return $result;
        }
        $where = Pi::db()->where();
        foreach ($query as $key => $val) {
            $where->equalTo($key, $val)->or;
        }

        $found = 0;
        $row = Pi::model('user_account')->select($where)->current();
        if ($row) {
            $uid = $this->params('id');
            if (!$uid || $uid == $row['id']) {
                $found = 1;
            }
        }
        $result = array(
            'status'    => $found,
        );

        return $result;
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
        $limit  = Pi::config('list_limit', 'user');
        $offset = (int) ($page -1) * $limit;

        list($users, $count) = $this->getUsers($condition, $limit, $offset);

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
            $result['message'] = _a('Enable user failed: invalid uid.');
            return $result;
        }

        $uids  = explode(',', $uids);
        $count = 0;
        $enableUids = array();
        foreach ($uids as $uid) {
            $status = Pi::api('user', 'user')->enableUser($uid);
            if ($status) {
                $count++;
                $enableUids[] = $uid;
            }
        }
        if ($enableUids) {
            Pi::service('event')->trigger('user_enable', $enableUids);
        }

        $usersStatus = $this->getUserStatus($uids);
        $result['users_status'] = $usersStatus;
        $result['status']  = 1;
        $result['message'] = sprintf(_a('%d users enabled.'), $count);

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
            $result['message'] = _a('Disable user failed: invalid uid.');
            return $result;
        }

        $uids  = explode(',', $uids);
        $count = 0;
        $disableUids[] = array();
        foreach ($uids as $uid) {
            $status = Pi::api('user', 'user')->disableUser($uid);
            if ($status) {
                $count++;
                $disableUids[] = $uid;
            }
        }
        if ($disableUids) {
            Pi::service('event')->trigger('user_disable', $disableUids);
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
                $result['deleted_uids'][] = $uid;
                // Clear user other info: user data, role, log, privacy, timeline
                $this->deleteUser($uid, 'user_data');
                $this->deleteUser($uid, 'user_role');
                $this->deleteUser($uid, 'user_log', 'user');
                $this->deleteUser($uid, 'privacy_user', 'user');
                $this->deleteUser($uid, 'timeline_log', 'user');
            }
        }
        if (!empty($result['deleted_uids'])) {
            Pi::service('event')->trigger(
                'user_delete',
                $result['deleted_uids']
            );
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
            $result['message'] = _a('Activate user failed: invalid uid.');
            return $result;
        }

        $uids = array_unique(explode(',', $uids));
        if (empty($uids)) {
            $result['message'] = _a('Activate user failed: invalid uid.');
            return $result;
        }

        $count = 0;
        $activateUids = array();
        foreach ($uids as $uid) {
            $status = Pi::api('user', 'user')->activateUser($uid);
            if ($status) {
                $count++;
                $activateUids[] = $uid;
            }
        }
        if ($activateUids) {
            Pi::service('event')->trigger('user_activate', $activateUids);
            $this->sendNotification($activateUids);
        }

        $usersStatus = $this->getUserStatus($uids);
        $result['users_status'] = $usersStatus;
        $result['status']  = 1;
        $result['message'] = sprintf(_a('%d users activated.'), $count);

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
            'status'    => 0,
            'data'      => array(),
            'message'   => '',
        );

        if (!$uids || !$type || !$role) {
            $result['message'] = _a('Role assignment failed: invalid parameters.');
            return $result;
        }

        $uids = array_unique(explode(',', $uids));
        if (!$uids) {
            $result['message'] = _a('Role assignment failed: invalid user ids.');
            return $result;
        }

        if (!in_array($type, array('add', 'remove'))) {
            $result['message'] = _a('Role assignment failed: invalid operation.');
            return $result;
        }

        // Add user role
        $roleAssignUids = array();
        if ($type == 'add') {
            foreach ($uids as $uid) {
                $status = Pi::api('user', 'user')->setRole($uid, $role);
                if (!$status) {
                    $result['message'] = _a('Role assignment failed.');
                    return $result;
                } else {
                    $roleAssignUids[] = $uid;
                }
            }
        }

        if ($roleAssignUids) {
            Pi::service('event')->trigger('role_assign', $roleAssignUids);
        }

        // Remove user role
        $roleRemoveUids = array();
        if ($type == 'remove') {
            foreach ($uids as $uid) {
                $status = Pi::api('user', 'user')->revokeRole($uid, $role);
                if (!$status) {
                    $result['message'] = _a('Role assignment failed.');
                    return $result;
                } else {
                    $roleRemoveUids[] = $uid;
                }
            }
        }

        if ($roleRemoveUids) {
            Pi::service('event')->trigger('role_remove', $roleRemoveUids);
        }

        $users = array();
        array_walk($uids, function ($uid) use (&$users) {
            $users[$uid] = array('id' => $uid);
        });
        $data = $this->renderRole($users);
        $result['data'] = $data;
        $result['status']  = 1;
        $result['message'] = _a('Role assignment succeeded.');

        return $result;

    }
    /**
     * Get users and count according to conditions
     *
     * @param $condition
     * @param int $limit
     * @param int $offset
     *
     * @return array    User list and count
     */
    protected function getUsers($condition, $limit = 0, $offset = 0)
    {
        $users = array();
        $count = 0;

        $modelAccount = Pi::model('user_account');
        $modelRole    = Pi::model('user_role');

        $where = array();
        $where['time_deleted'] = 0;
        if ($condition['active'] == 'active') {
            $where['active'] = 1;
        } elseif ($condition['active'] == 'inactive') {
            $where['active'] = 0;
        }
        if ($condition['enable'] == 'enable') {
            $where['time_disabled'] = 0;
        } elseif ($condition['enable'] == 'disable') {
            $where['time_disabled > ?'] = 0;
        }
        if ($condition['activated'] == 'activated') {
            $where['time_activated > ?'] = 0;
        } elseif ($condition['activated'] == 'pending') {
            $where['time_activated'] = 0;
        }
        if (!empty($condition['register_date'])) {
            $where['time_created >= ?'] = $this->canonizeRegisterDate(
                $condition['register_date']
            );
        }
        if (!empty($condition['uid'])) {
            if ($condition['front_role'] || $condition['admin_role']) {
                $where['account.id'] = (int) $condition['uid'];
            } else {
                $where['id'] = (int) $condition['uid'];
            }
        }
        if (!empty($condition['email'])) {
            $where['email like ?'] = '%' .$condition['email'] . '%';
        }
        if (!empty($condition['identity'])) {
            $where['identity like ?'] = '%' . $condition['identity'] . '%';
        }
        if (!empty($condition['name'])) {
            $where['name like ?'] = '%' . $condition['name'] . '%';
        }
        if (!empty($condition['time_created_from'])) {
            $where['time_created >= ?'] = strtotime($condition['time_created_from']);
        }
        if (!empty($condition['time_created_to'])) {
            $where['time_created <= ?'] = strtotime($condition['time_created_to'] . ' +1 day');
        }

        $whereAccount = Pi::db()->where()->create($where);
        $where = Pi::db()->where();
        $where->add($whereAccount);

        $select = Pi::db()->select();
        if (!empty($condition['front_role'])) {
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

        if (!empty($condition['admin_role'])) {
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

        if (!empty($condition['ip_register'])) {
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

        // Fetch count
        $select->from(
            array('account' => $modelAccount->getTable())
        )->columns(
            array('count' => Pi::db()->expression('COUNT(account.id)'))
        );

        $rowset = Pi::db()->query($select);
        if ($rowset) {
            $row = $rowset->current();
            $count = (int) $row['count'];
        }

        // Fetch users
        if ($count) {
            $select->columns(
                array('id')
            );
            $select->order('account.id DESC');
            if ($limit) {
                $select->limit($limit);
            }
            if ($offset) {
                $select->offset($offset);
            }
            $rowset = Pi::db()->query($select);
            $uids = array();
            foreach ($rowset as $row) {
                $uids[] = (int) $row['id'];
            }

            if ($uids) {
                $columns = array(
                    'identity',
                    'name',
                    'email',
                    'active',
                    'time_disabled',
                    'time_activated',
                    'time_created',
                    'ip_register',
                    'id'
                );
                $users = Pi::api('user', 'user')->get($uids, $columns);
                array_walk($users, function (&$user, $uid) {
                    $user['link'] = Pi::service('user')->getUrl('profile', array(
                        'id'    => $uid,
                    ));
                    $user['active']         = (bool) $user['active'];
                    $user['time_disabled']  = $user['time_disabled']
                        ? _date($user['time_disabled']) : 0;
                    $user['time_activated']  = $user['time_activated']
                        ? _date($user['time_activated']) : 0;
                    $user['time_created']  = $user['time_created']
                        ? _date($user['time_created']) : 0;
                });
                $users = $this->renderRole($users);
            }

        }
        $result = array($users, $count);

        return $result;
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
                0, 0, 0,
                date('m'),
                date('d'),
                date('Y')
            );
        }

        if ($registerDate == 'last_week') {
            $time = mktime(
                0, 0, 0,
                date('m'),
                date('d') - 7,
                date('Y')
            );
        }

        if ($registerDate == 'last_month') {
            $time = mktime(
                0, 0, 0,
                date('m') - 1,
                date('d'),
                date('Y')
            );
        }

        if ($registerDate == 'last_3_month') {
            $time = mktime(
                0, 0, 0,
                date('m') - 3,
                date('d'),
                date('Y')
            );
        }

        if ($registerDate == 'last_year') {
            $time = mktime(
                0, 0, 0,
                date('m'),
                date('d'),
                date('Y') - 1
            );
        }

        return $time;

    }

    /**
     * Render roles for users
     */
    protected function renderRole(array $users)
    {
        if (!$users) {
            return $users;
        }

        $uids = array_keys($users);
        $roleList = array();
        $roles = Pi::registry('role')->read();
        $rowset = Pi::model('user_role')->select(array('uid' => $uids));
        foreach ($rowset as $row) {
            $uid     = $row['uid'];
            $section = $row['section'];
            $roleKey = $section . '_roles';
            $roleList[$uid][$roleKey][] = $roles[$row['role']]['title'];
        }
        array_walk($users, function (&$user, $uid) use ($roleList) {
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
     * @param int[] $uids
     * @return array
     */
    protected function getUserStatus($uids)
    {
        $uids  = (array) $uids;
        $users = Pi::api('user', 'user')->get(
            $uids,
            array(
                'active', 'time_activated', 'time_disabled'
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

    /**
     * Send notification email
     *
     * @param int|int[] $uid
     *
     * @return bool
     */
    protected function sendNotification($uid)
    {
        if (!Pi::user()->config('register_notification')) {
            return true;
        }
        $uids   = (array) $uid;
        $users  = Pi::user()->get($uids, array('identity', 'email'));
        $template = 'register-success-html';
        $failedUsers = array();
        foreach ($users as $id => $data) {
            $redirect = Pi::user()->data()->get($id, 'register_redirect') ?: '';
            $url = Pi::api('user', 'user')->getUrl('login', array(
                'redirect'  => $redirect,
                'section'   => 'front',
            ));
            $url = Pi::url($url, true);
            $params = array(
                'username'  => $data['identity'],
                'login_url' => $url,
            );

            // Load from HTML template
            $template   = Pi::service('mail')->template($template, $params);
            $subject    = $template['subject'];
            $body       = $template['body'];
            $type       = $template['format'];

            //Pi::user()->data()->set($id, 'noti-email', $template); continue;

            // Send email
            $message    = Pi::service('mail')->message($subject, $body, $type);
            $message->addTo($data['email']);
            $transport  = Pi::service('mail')->transport();
            try {
                $transport->send($message);
            } catch (\Exception $e) {
                $failedUsers[] = $id;
            }
        }
        $result = $failedUsers ? false : true;

        return $result;

    }
}