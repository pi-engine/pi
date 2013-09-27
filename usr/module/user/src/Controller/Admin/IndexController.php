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
use Module\User\Form\MemberForm;

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
        $this->view()->setTemplate('index-index');
    }
    /**
     * Activated user manage
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
        $condition['search']        = _get('search') ?: '';

        // Exchange search
        if ($condition['search']) {
            // Check email or username
            if (!preg_match('/.+@.+/', $condition['search'])) {
                $condition['identity'] = $condition['search'];
            } else {
                $condition['email'] = $condition['search'];
            }
        }

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

        foreach ($condition as $key => $value) {
            if ($value) {
                $params[$key] = $value;
            }
        }



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

        // Exchange search
        if ($condition['search']) {
            // Check email or username
            if (!preg_match('/.+@.+/', $condition['search'])) {
                $condition['identity'] = $condition['search'];
            } else {
                $condition['email'] = $condition['search'];
            }
        }

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

        foreach ($condition as $key => $value) {
            if ($value) {
                $params[$key] = $value;
            }
        }

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

        $condition['pending']       = 'pending';
        $condition['enable']        = _get('enable') ?: '';
        $condition['front_role']    = _get('front_role') ?: '';
        $condition['admin_role']    = _get('admin_role') ?: '';
        $condition['register_date'] = _get('register_date') ?: '';
        $condition['search']        = _get('search') ?: '';


        // Exchange search
        if ($condition['search']) {
            // Check email or username
            if (!preg_match('/.+@.+/', $condition['search'])) {
                $condition['identity'] = $condition['search'];
            } else {
                $condition['email'] = $condition['search'];
            }
        }

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

        foreach ($condition as $key => $value) {
            if ($value) {
                $params[$key] = $value;
            }
        }

        $data = array(
            'users'       => array_values($users),
            'paginator'   => $paginator,
            'condition'   => $condition,
        );

        return $data;

    }

    /**
     * Add new user action
     *
     */
    public function addUserAction()
    {
        $result = array(
            'status' => 0,
            'message' => __('Add user failed'),
        );

        $identity   = _post('identity');
        $email      = _post('email');
        $credential = _post('credential');
        $activate   = _post('activate');
        $enable     = _post('enable');
        $name       = _post('name');

        if (!$identity || !$email || !$credential || !$name) {
            return $result;
        }

        // Check identity, email, display name
        $model = Pi::model('user_account');
        $row = $model->find($identity, 'identity');
        if ($row) {
            return $result;
        }

        $row = $model->find($email, 'email');
        if ($row) {
            return $result;
        }

        $row = $model->find($name, 'name');
        if ($row) {
            return $result;
        }

        // Set data
        $data = array();
        if ($activate == 1) {
            $data['time_activated'] = time();
        } else {
            $data['time_activated'] = 0;
        }

        if ($enable == 1) {
            $data['time_disabled'] = 0;
        } else {
            $data['time_disabled'] = time();
        }

        if ($activate == 1 && $enable == 1) {
            $data['active'] = 1;
        } else {
            $data['active'] = 0;
        }
        $data['identity'] = $identity;
        $data['email']    = $email;
        $data['name']     = $name;

        // Save user info
        $status = Pi::api('user', 'user')->addUser($data);

        if ($status) {
            $result['status'] = 1;
            $result['message'] = __('Add user successfully');
        }

        return $result;
    }

    /**
     * Display search form
     *
     * @return \Zend\Mvc\Controller\Plugin\Redirect
     */
    public function searchAction()
    {
        // Initialise search options
        $this->view()->setTemplate('index-search');

        $form = new SearchForm('search');
        // Set admin role default
        $options = $form->get('front-role')->getValueOptions();
        array_shift($options);
        $options = array_merge(array('any' => __('Any role')), $options);d($options);
        $form->get('front-role')->setValueOptions($options);

        // Set admin role default
        $options = $form->get('admin-role')->getValueOptions();
        array_shift($options);
        $options = array_merge(array('any' => __('Any role')), $options);
        $form->get('admin-role')->setValueOptions($options);

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);

            if ($form->isValid()) {
                $data = $form->getData();
                $condition = $this->canonizeSearchData($data);
                $params = array(
                    'controller' => 'index',
                    'action'     => 'search.list',
                );

                $params = array_merge($params, $condition);
                return $this->redirect('', $params);
            }
        }

        $this->view()->assign(array(
            'form' => $form,
        ));
    }

    /**
     * Display search result list
     */
    public function searchListAction()
    {
        $page   = (int) $this->params('p', 1);
        $limit  = 10;
        $offset = (int) ($page -1) * $limit;

        $condition['active']            = _get('active') ?: '';
        $condition['enable']            = _get('enable') ?: '';
        $condition['activated']         = _get('activated') ?: '';
        $condition['identity']          = _get('identity') ?: '';
        $condition['name']              = _get('name') ?: '';
        $condition['front_role']        = _get('front_role') ?: '';
        $condition['admin_role']        = _get('admin_role') ?: '';
        $condition['email']             = _get('email') ?: '';
        $condition['time_created_form'] = _get('time_created_form') ?: '';
        $condition['time_created_to']   = _get('time_created_to') ?: '';
        $condition['ip_register']       = _get('ip_register') ?: '';

        // Get user ids
        $uids  = $this->getUids($condition, $limit, $offset);

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

        foreach ($condition as $key => $value) {
            if ($value) {
                $params[$key] = $value;
            }
        }

        $data = array(
            'users'       => array_values($users),
            'paginator'   => $paginator,
            'front_roles' => $this->getRoleSelectOptions(),
            'admin_roles' => $this->getRoleSelectOptions('admin'),
            'condition'   => $condition,
        );

        return $data;

        $this->view()->setTemplate('index-search-list');
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

        $uids = explode(',', $uids);
        $count = 0;
        foreach ($uids as $uid) {
            $status = Pi::api('user', 'user')->enableUser($uid);
            if ($status) {
                $count++;
            }
        }
        $return['status'] = $count ? 1 : 0;
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
        $return['status'] = $count ? 1 : 0;
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
            $status = Pi::api('user', 'user')->deleteUser();
            if ($status) {
                $count++;
            }
        }
        $return['status'] = $count ? 1 : 0;
        $return['message'] = sprintf(__('%d delete user successfully'), $count);

        return $return;

    }

    /**
     * Check username, email, display name duplication
     *
     * @return array
     */
    public function checkDuplicationAction()
    {
        $status = 0;

        $identity = _get('identity');
        $email    = _get('email');
        $name     = _get('name');
        $uid      = (int) _get('uid');

        if (!$identity && !$email && !$name ) {
            return array('status' => $status);
        }

        $model = Pi::model('user_account');
        if ($identity) {
            $row = $model->find($identity, 'identity');
            if (!$row) {
                $status = 1;
            } else {
                $status = ($row['id'] == $uid) ? 1 : 0;
            }
        }

        if ($email) {
            $row = $model->find($email, 'email');
            if (!$row) {
                $status = 1;
            } else {
                $status = ($row['id'] == $uid) ? 1 : 0;
            }
        }

        if ($name) {
            $row = $model->find($name, 'name');
            if (!$row) {
                $status = 1;
            } else {
                $status = ($row['id'] == $uid) ? 1 : 0;
            }
        }

        return array(
            'status' => $status,
        );

    }

    /**
     * Set role
     *
     * @return array
     */
    public function setRoleAction()
    {
        $uid     = _post('uid');
        $role    = _post('role');
        $section = _post('section');

        $result = array(
            'status'  => 0,
            'message' => ''
        );

        if (!$uid || !$role || !$section) {
            return $result;
        }

        $status = Pi::api('user', 'user')->setRole($uid, $role, $section);
        if ($status) {
            $result['status'] = 1;
            $result['message'] = __('Set role successfully');
        } else {
            $result['status'] = 0;
            $result['message'] = __('Set role failed');
        }

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
        $return['status'] = $count ? 1 : 0;
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
        $uids    = _post('uids');
        $type    = _post('type');
        $role    = _post('role');

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

        $result['status'] = 1;
        $result['message'] = __('Assign role successfully');

        return $result;

    }

    /**
     * Get user information
     *
     * @param int[] $ids
     * @return array
     */
    protected function getUser($ids)
    {
        $users = array();
        if (!$ids) {
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

        $users = Pi::api('user', 'user')->get(
            $ids,
            array_keys($columns)
        );

        $roles = Pi::registry('role')->read();
        $rowset = Pi::model('user_role')->select(array('uid' => $uids));
        foreach ($rowset as $row) {
            $uid = $row['uid'];
            $section = $row['section'];
            $roleKey = $section . '_role';
            $users[$uid][$roleKey][] = $roles[$row['role']]['title'];
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
        if ($condition['pending'] == 'pending') {
            $where['time_activated'] = 0;
        }
        if ($condition['register_date']) {
            $where['time_created >= ?'] = $this->canonizeRegisterDate(
                $condition['register_date']
            );
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
            $whereRoleFront = Pi::db()->where()->create(array(
                'front.role'    => $condition['front_role'],
                'front.section' => 'front',
            ));
            $where->add($whereRoleFront);
        }
        if ($condition['admin_role']) {
            $whereRoleAdmin = Pi::db()->where()->create(array(
                'admin.role'    => $condition['admin_role'],
                'admin.section' => 'admin',
            ));
            $where->add($whereRoleAdmin);
        }
        if ($condition['front_role']) {
            $select->join(
                array('front' => $modelRole->getTable()),
                'front.uid=account.id',
                array()
            );
        }
        if ($condition['admin_role']) {
            $select->join(
                array('admin' => $modelRole->getTable()),
                'admin.uid=account.id',
                array()
            );
        }
        if ($limit) {
            $select->limit($limit);
        }
        if ($offset) {
            $select->offset($offset);
        }

        $select->where($where);

        $rowset = Pi::db()->query($select);

        foreach ($rowset as $row) {
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
        if ($condition['pending'] == 'pending') {
            $where['time_activated'] = 0;
        }
        if ($condition['register_date']) {
            $where['time_created >= ?'] = $this->canonizeRegisterDate(
                $condition['register_date']
            );
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
            $whereRoleFront = Pi::db()->where()->create(array(
                'front.role'    => $condition['front_role'],
                'front.section' => 'front',
            ));
            $where->add($whereRoleFront);
        }

        if ($condition['admin_role']) {
            $whereRoleAdmin = Pi::db()->where()->create(array(
                'admin.role'    => $condition['admin_role'],
                'admin.section' => 'admin',
            ));
            $where->add($whereRoleAdmin);
        }

        if ($condition['front_role']) {
            $select->join(
                array('front' => $modelRole->getTable()),
                'front.uid=account.id',
                array()
            );
        }

        if ($condition['admin_role']) {
            $select->join(
                array('admin' => $modelRole->getTable()),
                'admin.uid=account.id',
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

        $model = Pi::model('role');
        $rowset = $model->select(array());
        foreach ($rowset as $row) {
            if ($row['section'] == 'admin') {
                $adminRole[] = array(
                    'name' => $row['name'],
                    'title' => $row['title'],
                );
            } else {
                $frontRole[] = array(
                    'name' => $row['name'],
                    'title' => $row['title'],
                );
            }
        }

        return array($frontRole, $adminRole);
    }

    /**
     * Canonize register date
     *
     * @params $rengisterDate
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
     * Canonize search data
     *
     * @param $data
     * @return array
     */
    protected function canonizeSearchData($data)
    {
        $condition = array();
        if ($data['active'] == 'any') {
            $condition['active'] = '';
        } else {
            $condition['active'] = $data['active'];
        }
        if ($data['enable'] == 'any') {
            $condition['enable'] = '';
        } else {
            $condition['enable'] = $data['enable'];
        }
        if ($data['activated'] == 'any') {
            $condition['activated'] = '';
        } else {
            $condition['activated'] = $data['activated'];
        }
        if ($data['front_role'] == 'any') {
            $condition['front_role'] = '';
        } else {
            $condition['front_role'] = $data['front_role'];
        }
        if ($data['admin_role'] == 'any') {
            $condition['admin_role'] = '';
        } else {
            $condition['admin_role'] = $data['admin_role'];
        }

        $condition['identity']          = $data['identity'];
        $condition['name']              = $data['name'];
        $condition['email']             = $data['email'];
        $condition['ip_register']       = $data['ip_register'];
        $condition['time_created_from'] = strtotime($data['time_created_from']);
        $condition['time_created_to']   = strtotime($data['time_created_to']);

        return $condition;

    }
}