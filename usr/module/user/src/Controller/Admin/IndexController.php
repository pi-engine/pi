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
use Pi\Paginator\Paginator;
use Pi\Acl\Acl;
use Module\User\Form\MemberForm;

/**
* User manage cases controller
*
* @author Liu Chuang <liuchuang@eefocus.com>
*/
class IndexController extends ActionController
{
    /**
     * Activated user manage
     *
     * @return array|void
     */
    public function indexAction()
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
        $users = $this->getUser($uids, 'all');

        // Set paginator
        $paginatorOption = array(
            'count'      => $count,
            'limit'      => $limit,
            'page'       => $page,
        );

        foreach ($condition as $key => $value) {
            if ($value) {
                $params[$key] = $value;
            }
        }

        $paginator = $this->setPaginator($paginatorOption, $params);

        $this->view()->assign(array(
            'users'      => $users,
            'paginator'  => $paginator,
            'page'       => $page,
            'front_role' => $this->getRoleSelectOptions(),
            'admin_role' => $this->getRoleSelectOptions('admin'),
            'count'      => $count,
            'condition'  => $condition,
        ));
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
        $users = $this->getUser($uids, 'all');

        // Set paginator
        $paginatorOption = array(
            'count'      => $count,
            'limit'      => $limit,
            'page'       => $page,
        );

        foreach ($condition as $key => $value) {
            if ($value) {
                $params[$key] = $value;
            }
        }

        $paginator = $this->setPaginator($paginatorOption, $params);

        $this->view()->assign(array(
            'users'      => $users,
            'paginator'  => $paginator,
            'page'       => $page,
            'front_role' => $this->getRoleSelectOptions(),
            'admin_role' => $this->getRoleSelectOptions('admin'),
            'count'      => $count,
            'condition'  => $condition,
        ));
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
        $users = $this->getUser($uids, 'all');

        // Set paginator
        $paginatorOption = array(
            'count'      => $count,
            'limit'      => $limit,
            'page'       => $page,
        );

        foreach ($condition as $key => $value) {
            if ($value) {
                $params[$key] = $value;
            }
        }

        $paginator = $this->setPaginator($paginatorOption, $params);

        $this->view()->assign(array(
            'users'      => $users,
            'paginator'  => $paginator,
            'page'       => $page,
            'front_role' => $this->getRoleSelectOptions(),
            'admin_role' => $this->getRoleSelectOptions('admin'),
            'count'      => $count,
            'condition'  => $condition,
        ));
    }

    /**
     * Add new user action
     *
     */
    public function addUserAction()
    {
        $this->view()->setTemplate('index-add');
        $form = new MemberForm('add-user');
        $status = 0;
        $isPost = 0;

        $options = $form->get('admin-role')->getValueOptions();
        array_shift($options);
        $options = array_merge(array('none' => __('Admin role')), $options);
        $form->get('admin-role')->setValueOptions($options);

        if ($this->request->isPost()) {
            $post = $this->request->getPost();
            $form->setData($post);
            $form->setInputFilter(new MemberFilter());

            if ($form->isValid()) {
                $values = $form->getData();
                $status = Pi::api('user', 'user')->addUser($values);
            }
            $isPost = 1;
        }

        $this->view()->assign(array(
            'form'   => $form,
            'status' => $status,
            'is_post' => $isPost,
        ));
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
        $users = $this->getUser($uids, 'search');

        // Set paginator
        $paginatorOption = array(
            'count'      => $count,
            'limit'      => $limit,
            'page'       => $page,
        );

        foreach ($condition as $key => $value) {
            if ($value) {
                $params[$key] = $value;
            }
        }

        $paginator = $this->setPaginator($paginatorOption, $params);

        $this->view()->assign(array(
            'users'      => $users,
            'paginator'  => $paginator,
            'page'       => $page,
            'front_role' => $this->getRoleSelectOptions(),
            'admin_role' => $this->getRoleSelectOptions('admin'),
            'count'      => $count,
            'condition'  => $condition,
        ));

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
        $uids = _post('uids');

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
     * Get user information according to type
     * Type: active, pending search
     *
     * @param $ids
     * @param $type
     * @return array
     */
    protected function getUser($ids, $type)
    {
        $users = array();
        if (!$ids || !$type) {
            return $users;
        }

        $columns = array();
        // For activated list
        if ($type == 'all') {
            $columns = array(
                'identity'       => '',
                'name'           => '',
                'email'          => '',
                'active'         => '',
                'time_disabled'  => '',
                'time_activated' => '',
                'time_created'   => '',
                'id'            => '',
            );
        }

        // For pending list
        if ($type == 'pending') {
            $columns = array(
                'identity'       => '',
                'name'           => '',
                'email'          => '',
                'time_disabled'  => '',
                'time_activated' => '',
                'front_role'     => '',
                'admin_role'     => '',
                'time_created'   => '',
                'id'             => '',
            );

        }

        if ($type == 'search') {
            $columns = array(
                'identity'       => '',
                'name'           => '',
                'email'          => '',
                'active'         => '',
                'time_disabled'  => '',
                'time_activated' => '',
                'time_created'   => '',
                'id'             => '',
                'ip_register'    => '',
            );
        }

        $users = Pi::api('user', 'user')->get(
            $ids,
            array_keys($columns)
        );

        foreach ($users as &$user) {
            $user = array_merge($columns, $user);

            // Get role
            $user['front_role'] = Pi::api('user', 'user')->getRole(
                $user['id'],
                'front'
            );
            $user['admin_role'] = Pi::api('user', 'user')->getRole(
                $user['id'],
                'admin'
            );
        }

        return $users;

    }

    /**
     * Get system all role for select form
     *
     * @param $section
     * @return array
     */
    protected function getRoleSelectOptions($section = 'front')
    {
        $model = Pi::model('acl_role');
        if ($section == 'front') {
            // Get front role
            $rowset = $model->select(array('section' => 'front'));
            foreach ($rowset as $row) {
                $options[$row->name] = __($row->title);
            }
        }
        if ($section == 'admin') {
            // Get admin role
            $rowset = $model->select(array('section' => 'admin'));
            foreach ($rowset as $row) {
                $options[$row->name] = __($row->title);
            }
        }

        return $options;

    }

    /**
     * Set paginator
     *
     * @param $option
     * @return \Pi\Paginator\Paginator
     */
    protected function setPaginator($option, $params)
    {

        $paginator = Paginator::factory(intval($option['count']), array(
            'limit' => $option['limit'],
            'page'  => $option['page'],
            'url_options'   => array(
                'params'    => $params
            ),
        ));
        return $paginator;
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

    public function testAction()
    {
        $this->view()->setTemplate(false);
//        vd(Pi::api('user', 'user')->getUser(1000)->id);
//        $modelAccount = Pi::model('user_account');
//        $modelRole = Pi::model('user_role');
//
//
//        $whereRoleAdmin = Pi::db()->where()->create(array(
//            'admin.role'     => 'staff',
//            'admin.section'  => 'admin',
//        ));
//
//        $whereRoleFront = Pi::db()->where()->create(array(
//            'front.role'     => 'member',
//            'front.section'  => 'front',
//        ));
//
//        $where = Pi::db()->where();
//        $where->add(array('account.active' => 1))
//            ->add($whereRoleAdmin)
//            ->add($whereRoleFront);
//
//        $select = Pi::db()->select();
//        $select->from(
//            array('account' => $modelAccount->getTable()),
//            array('id')
//        );
//        //$select->columns(array('id'));
//        $select->join(
//            array('front' => $modelRole->getTable()),
//            'front.uid=account.id',
//            array()
//        );
//        $select->join(
//            array('admin' => $modelRole->getTable()),
//            'admin.uid=account.id',
//            array()
//        );
//        $select->where($where);
//        $rowset = Pi::db()->query($select);

        /*
        $modelAccount = $this->getModel('account');
        $select  = Pi::db()->select();
        $select->from(array('account' => $modelAccount->getTable()));
        $select->columns(array('account.id'));

        $modelRole = Pi::model('user_role');
        $select->join(
            array('role1' => $modelRole->getTable()),
            'role1.uid' . '=' . 'account.id'
        );

        $select->join(
            array('role2' => $modelRole->getTable()),
            'role2.uid' . '=' . 'account.id'
        );

        $where = array(
            'account.active' => 1,
            'role1.role'     => 'staff',
            'role1.section'  => 'admin',
            'role2.role'     => 'member',
            'role2.section'  => 'front',
        );

        $select->where($where);
        $rowset = Pi::db()->query($select);
        */
    }

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