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
        $limit  = 10;
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
            'limit'      => $limit,
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
        $limit  = 10;
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
            'limit'      => $limit,
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
        $limit  = 10;
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
            'limit'      => $limit,
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
        if (count($rowset) != 0 || empty($roles)) {
            $result['message'] = __('Add user failed');
            return $result;
        }

        $data = array(
            'identity'   => $identity,
            'name'       => $name,
            'email'      => $email,
            'credential' => $credential,
        );

        // Add user
        $uid = Pi::api('user', 'user')->addUser($data, false);
        if (!$uid) {
            $result['message'] = __('Add user failed');
            return $result;
        }

        // Activate
        if ($activated == 1) {
            Pi::api('user', 'user')->activateUser($uid);
        }

        // Enable
        if ($enable == 1) {
            Pi::api('user', 'user')->enableUser($uid);
        }

        // Set role
        Pi::api('user', 'user')->setRole($uid, $roles);

        $result['status']  = 1;
        $result['message'] = __('Add user successfully');

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
        $limit  = 10;
        $offset = (int) ($page -1) * $limit;

        // Get user ids
        $uids  = $this->getUids($condition, $limit, $offset);;

        // Get user count
        $count = $this->getCount($condition);

        // Get user information
        $users = $this->getUser($uids);

        // Set paginator
        $paginator = array(
            'count'      => $count,
            'limit'      => $limit,
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
     * Search user info buy display name
     *
     * @return array
     */
    public function searchUserAction()
    {
        $name = _get('name');
        $result = array();

        if (!$name) {
            return $result;
        }

        // Check name
        $rowset = $this->getModel('account')->find($name, 'name');
        $model = $this->getModel('account');
        $where = array(
            'name' => $name,
            'time_deleted' => 0,
        );
        $select = $model->select()->where($where);
        $rowset = $model->selectWith($select)->current();

        if (!$rowset) {
            return $result;
        } else {
            $uid = $rowset->id;
        }

        // Get fields
        $where = array(
            'active'     => 1,
            'is_display' => 1,
        );
        $rowset = $this->getModel('field')->select($where);
        foreach ($rowset as $row) {
            $columns[] = $row['name'];
        }

        $fieldMeta = Pi::api('user', 'user')->getMeta('', 'display');
        $data = Pi::api('user', 'user')->get($uid, $columns);
        if (isset($data['id'])) {
            unset($data['id']);
        }

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // Compound
                // Get compound meta
                $compoundMeta = Pi::registry('compound', 'user')->read($key);
                $result[$key] = array(
                    'title' => $fieldMeta[$key]['title'],
                );


                foreach ($value as $items) {
                    foreach ($items as $col => $val) {
                        $compoundItems[] = array(
                            'title' => $compoundMeta[$col]['title'],
                            'value' => $val,
                        );
                    }
                    $result[$key]['items'][] = $compoundItems;
                }
            } else {
                $result[$key] = array(
                    'title' => $fieldMeta[$key]['title'] ? : ucfirst($key),
                    'value' => $value,
                );
            }
        }

        $result = array_values($result);

        return $result;

    }

    /**
     * Enable users
     *
     * @return array
     */
    public function enableAction()
    {
        $return = array(
            'status'  => 0,
            'message' => '',
        );

        $uids = _post('ids', '');

        if (!$uids) {
            $return['message'] = __('Enable user failed');
            return $return;
        }

        $uids  = explode(',', $uids);
        $count = 0;
        foreach ($uids as $uid) {
            $status = Pi::api('user', 'user')->enableUser($uid);
            if ($status) {
                $count++;
            }
        }
        $return['status'] = 1;
        $return['message'] = sprintf(__('%d enable user successfully'), $count);

        return $return;

    }

    /**
     * Disable user
     *
     * @return array
     */
    public function disableAction()
    {
        $return = array(
            'status'  => 0,
            'message' => ''
        );
        $uids = _post('ids', '');

        if (!$uids) {
            $return['message'] = __('Disable user failed');
            return $return;
        }

        $uids  = explode(',', $uids);
        $count = 0;
        foreach ($uids as $uid) {
            $status = Pi::api('user', 'user')->disableUser($uid);
            if ($status) {
                $count++;
            }
        }
        $return['status']  = 1;
        $return['message'] = sprintf(__('%d disable user successfully'), $count);

        return $return;

    }

    /**
     * Delete user
     *
     * @return array
     */
    public function deleteUserAction()
    {
        $uids   = _post('ids');
        $return = array(
            'status'  => 0,
            'message' => '',
        );

        if (!$uids) {
            $return['message'] = __('Delete user failed');
            return $return;
        }

        $uids  = explode(',', $uids);
        $count = 0;
        foreach ($uids as $uid) {
            $status = Pi::api('user', 'user')->deleteUser($uid);
            if ($status) {
                $count++;
            }
        }
        $return['status']  = 1;
        $return['message'] = sprintf(__('%d delete user successfully'), $count);

        return $return;

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
            $result['message'] = __('Activate user failed');
            return $result;
        }

        $uids = array_unique(explode(',', $uids));
        if (empty($uids)) {
            $result['message'] = __('Activate user failed');
            return $result;
        }

        $count = 0;
        foreach ($uids as $uid) {
            $status = Pi::api('user', 'user')->activateUser($uid);
            if ($status) {
                $count++;
            }
        }
        $return['status']  = 1;
        $return['message'] = sprintf(__('%d activated user successfully'), $count);

        return $return;

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

        if (!$uids || !$type || !$role) {
            $result['message'] = __('Assign role failed');
            return $result;
        }

        $uids = array_unique(explode(',', $uids));
        if (!$uids) {
            $result['message'] = __('Assign role failed');
            return $result;
        }

        if (!in_array($type, array('add', 'remove'))) {
            $result['message'] = __('Assign role failed');
            return $result;
        }

        // Add user role
        if ($type == 'add') {
            foreach ($uids as $uid) {
                $status = Pi::api('user', 'user')->setRole($uid, $role);
                if (!$status) {
                    $result['message'] = __('Assign role failed');
                    return $result;
                }
            }
        }

        // Remove user role
        if ($type == 'remove') {
            foreach ($uids as $uid) {
                $status = Pi::api('user', 'user')->revokeRole($uid, $role);
                if (!$status) {
                    $result['message'] = __('Assign role failed');
                    return $result;
                }
            }
        }

        $result['status']  = 1;
        $result['message'] = __('Assign role successfully');

        return $result;

    }

    /**
     * Get user information
     *
     * @param int[] $ids
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

        // Get user data
        $data = Pi::api('user', 'user')->get(
            $uids,
            array_keys($columns)
        );
        foreach ($uids as $uid) {
            $users[$uid] = $data[$uid];
        }

        $rowset = Pi::model('user_role')->select(array('uid' => $uids));
        foreach ($rowset as $row) {
            $uid     = $row['uid'];
            $section = $row['section'];
            $roleKey = $section . '_roles';
            $users[$uid][$roleKey][] = $row['role'];
        }

        foreach ($users as &$user) {
            $user['active']         = (int) $user['active'];
            $user['time_disabled']  = (int) $user['time_disabled'];
            $user['time_activated'] = (int) $user['time_activated'];
            $user['time_created']   = (int) $user['time_created'];
            $user = array_merge($columns, $user);
        }

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
            $where['time_created >= ?'] = $condition['time_created_from'];
        }
        if ($condition['time_created_to']) {
            $where['time_created <= ?'] = $condition['time_created_to'];
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

        $select->order('account.time_created DESC');
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
            $where['time_created >= ?'] = $condition['time_created_from'];
        }
        if ($condition['time_created_to']) {
            $where['time_created <= ?'] = $condition['time_created_to'];
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
}