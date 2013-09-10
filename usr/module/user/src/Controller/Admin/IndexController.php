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

        $condition['state']        = _get('state') ? : '';
        $condition['front-role']   = _get('front-role') ?: '';
        $condition['admin-role']   = _get('admin-role') ?: '';
        $condition['time-created'] = _get('time-created') ?: '';
        $condition['search']       = _get('search') ?: '';

        // Exchange search
        if ($condition['search']) {
            // Check email or username
            if (preg_match('/.+@.+/', $condition['search'])) {
                $condition['identity'] = $condition['search'];
            } else {
                $condition['email'] = $condition['search'];
            }
        }

        // Get user ids
        $uids  = $this->getUids($condition, 'activated', $limit, $offset);

        // Get user count
        $count = $this->getCount($condition, 'activated');

        // Get user information
        $users = $this->getUser($uids, 'activated');

        // Set paginator
        $paginatorOption = array(
            'count'      => $count,
            'limit'      => $limit,
            'page'       => $page,
            //'controller' => 'index',
            //'action'     => 'index',
        );

        $paginator = $this->setPaginator($paginatorOption);
        $this->view()->assign(array(
            'users'     => $users,
            'paginator' => $paginator,
            'page'      => $page,
            'curNav'    => 'activated',
            'frontRole' => $this->getRoleSelectOptions(),
            'adminRole' => $this->getRoleSelectOptions('admin'),
            'count'     => $count,
            'condition' => $condition,
        ));

        vd($this->getRoleSelectOptions('admin'));
    }

    /**
     * Pending user list
     */
    public function pendingAction()
    {
        $page   = (int) $this->params('p', 1);
        $limit  = 10;
        $offset = (int) ($page -1) * $limit;

        $condition['front-role']   = _get('front-role') ?: '';
        $condition['admin-role']   = _get('admin-role') ?: '';
        $condition['time-created'] = _get('time-created') ?: '';
        $condition['search']       = _get('search') ?: '';

        // Exchange search
        if ($condition['search']) {
            // Check email or username
            if (preg_match('/.+@.+/', $condition['search'])) {
                $condition['identity'] = $condition['search'];
            } else {
                $condition['email'] = $condition['search'];
            }
        }

        // Get user ids
        $uids = $this->getUids($condition, 'pending', $limit, $offset);

        // Get user amount
        $count = $this->getCount($condition, 'pending');

        // Get user information
        $users = $this->getUser($uids, 'pending');

        // Set paginator
        $paginatorOption = array(
            'count'      => $count,
            'limit'      => $limit,
            'page'       => $page,
            //'controller' => 'index',
            //'action'     => 'index',
        );

        $paginator = $this->setPaginator($paginatorOption);
        $this->view()->assign(array(
            'users'     => $users,
            'paginator' => $paginator,
            'page'      => $page,
            'curNav'    => 'pending',
            'frontRole' => $this->getRoleSelectOptions(),
            'adminRole' => $this->getRoleSelectOptions('admin'),
            'count'     => $count,
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

    public function searchAction()
    {
        // Initialise search options
        $this->view()->setTemplate('index-search');
        $condition['state']      = _get('state') ?: '';
        $condition['front-role'] = _get('front-role') ?: '';
        $condition['admin-role'] = _get('admin-role') ?: '';

        $form = new SearchForm('search');
        // Set front role default
        $options = $form->get('front-role')->getValueOptions();
        array_shift($options);
        $options = array_merge(array('' => __('Front role')), $options);
        $form->get('front-role')->setValueOptions($options);
        // Set admin role default
        $options = $form->get('admin-role')->getValueOptions();
        array_shift($options);
        $options = array_merge(array('' => __('Admin role')), $options);
        $form->get('admin-role')->setValueOptions($options);

        $form->setData($options);

        $this->view()->assign(array(
            'form' => $form,
        ));
    }

    /**
     * Enable users
     *
     * @return array
     */
    public function enableAction()
    {
        $uids = _post('ids', '');
        if (!$uids) {
            return array(
                'status' => 0,
            );
        }

        $uids = explode(',', $uids);

        foreach ($uids as $uid) {
            Pi::api('usr', 'user')->enable($uid);
        }

        return array(
            'status' => 1,
        );
    }

    /**
     * Disable user
     *
     * @return array
     */
    public function disableAction()
    {
        $uids = _post('ids', '');

        if (!$uids) {
            return array(
                'status' => 0,
            );
        }
        $uids = explode(',', $uids);

        foreach ($uids as $uid) {
            Pi::api('user', 'user')->disableAction();
        }

        return array(
            'status' => 1,
        );

    }

    /**
     * Delete user
     *
     * @return array
     */
    public function deleteUserAction()
    {
        $uids = _post('ids');
        $return = array(
            'status' => 0
        );

        if (!$uids) {
            return $return;
        }

        $uids = explode(',', $uids);

        foreach ($uids as $uid) {
            Pi::api('user', 'user')->deleteUser();
        }
        $return['status'] = 1;

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
            'status' => 0,
        );

        if (!$uid || !$role || !$section) {
            return $result;
        }

        Pi::api('user', 'user')->setRole($uid, $role, $section);

        return $result;

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
        $return = array();
        if (!$ids || !$type) {
            return $return;
        }

        // For activated list
        if ($type == 'activated') {
            $return = array(
                'identity'      => '',
                'name'          => '',
                'email'         => '',
                'time_disabled' => '',
                'front_role'    => '',
                'admin_role'    => '',
                'register_ip'   => '',
                'time_created'  => '',
                'login_time'    => '',
                'id'            => '',
            );
        }

        // For pending list
        if ($type == 'pending') {
            $return = array(
                'identity'       => '',
                'name'           => '',
                'email'          => '',
                'time_activated' => '',
                'front_role'     => '',
                'admin_role'     => '',
                'register_ip'    => '',
                'time_created'   => '',
                'id'             => '',
            );

        }

        $users = Pi::api('user', 'user')->get(
            $ids,
            array_keys($return)
        );

        foreach ($users as &$user) {
            $user = array_merge($return, $user);

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
    protected function setPaginator($option)
    {

        $paginator = Paginator::factory(intval($option['count']), array(
            'limit' => $option['limit'],
            'page'  => $option['page'],
            'url_options'   => array(
                'params'    => array(
                    'uid'   => $option['uid'],
                ),
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
    protected function getUids($condition, $type, $limit = 0, $offset = 0)
    {
        $modelAccount = Pi::model('user_account');
        $modelRole    = Pi::model('user_role');

        $where = array();
        if ($type == 'activated') {
            $where['time_activated <> ?'] = 0;
        }

        if ($type == 'pending') {
            $where['time_activated'] = 0;
        }

        if ($condition['state'] == 'enable') {
            $where['time_disabled'] = 0;
        }

        if ($condition['state'] == 'disable') {
            $where['time_disabled > ?'] = 0;
        }

        if ($condition['time-created'] == 'today') {
            $where['time_created >= ?'] = mktime(
                0,0,0,
                date("m"),
                date("d"),
                date("Y")
            );
        }

        if ($condition['time-created'] == 'last-week') {
            $where['time_created >= ?'] = mktime(
                0,0,0,
                date("m"),
                date("d") - 7,
                date("Y")
            );
        }

        if ($condition['time-created'] == 'last-month') {
            $where['time_created >= ?'] = mktime(
                0,0,0,
                date("m") - 1,
                date("d"),
                date("Y")
            );
        }

        if ($condition['time-created'] == 'last-3-month') {
            $where['time_created >= ?'] = mktime(
                0,0,0,
                date("m") - 3,
                date("d"),
                date("Y")
            );
        }

        if ($condition['time-created'] == 'last-year') {
            $where['time_created >= ?'] = mktime(
                0,0,0,
                date("m"),
                date("d"),
                date("Y") - 1
            );
        }

        if ($condition['email']) {
            // Todo like match
            $where['email'] = $condition['email'];
        }

        if ($condition['identity']) {
            // Todo like match
            $where['identity'] = $condition['identity'];
        }

        $whereAccount = Pi::db()->where()->create($where);
        $where = Pi::db()->where();
        $where->add($whereAccount);

        $select = Pi::db()->select();
        $select->from(
            array('account' => $modelAccount->getTable()),
            array('id')
        );

        if ($condition['front-role']) {
            $whereRoleFront = Pi::db()->where()->create(array(
                'front.role'    => $condition['front-role'],
                'front.section' => 'front',
            ));
            $where->add($whereRoleFront);
        }

        if ($condition['admin-role']) {
            $whereRoleAdmin = Pi::db()->where()->create(array(
                'admin.role'    => $condition['admin-role'],
                'front.section' => 'front',
            ));
            $where->add($whereRoleAdmin);
        }

        if ($condition['admin-role']) {
            $select->join(
                array('front' => $modelRole->getTable()),
                'front.uid=account.id',
                array()
            );
        }

        if ($condition['front-role']) {
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
    protected function getCount($condition, $type)
    {
        $modelAccount = Pi::model('user_account');
        $modelRole    = Pi::model('user_role');

        //$select->from
        $where = array();
        if ($type == 'activated') {
            $where['time_activated <> ?'] = 0;
        }

        if ($type == 'pending') {
            $where['time_activated'] = 0;
        }

        if ($condition['state'] == 'enable') {
            $where['time_disabled'] = 0;
        }

        if ($condition['state'] == 'disable') {
            $where['time_disabled > ?'] = 0;
        }

        if ($condition['time-created'] == 'today') {
            $where['time_created >= ?'] = mktime(
                0,0,0,
                date("m"),
                date("d"),
                date("Y")
            );
        }

        if ($condition['time-created'] == 'last-week') {
            $where['time_created >= ?'] = mktime(
                0,0,0,
                date("m"),
                date("d") - 7,
                date("Y")
            );
        }

        if ($condition['time-created'] == 'last-month') {
            $where['time_created >= ?'] = mktime(
                0,0,0,
                date("m") - 1,
                date("d"),
                date("Y")
            );
        }

        if ($condition['time-created'] == 'last-3-month') {
            $where['time_created >= ?'] = mktime(
                0,0,0,
                date("m") - 3,
                date("d"),
                date("Y")
            );
        }

        if ($condition['time-created'] == 'last-year') {
            $where['time_created >= ?'] = mktime(
                0,0,0,
                date("m"),
                date("d"),
                date("Y") - 1
            );
        }

        if ($condition['email']) {
            // Todo like match
            $where['email'] = $condition['email'];
        }

        if ($condition['identity']) {
            // Todo like match
            $where['identity'] = $condition['identity'];
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

        if ($condition['front-role']) {
            $whereRoleFront = Pi::db()->where()->create(array(
                'front.role'    => $condition['front-role'],
                'front.section' => 'front',
            ));
            $where->add($whereRoleFront);
        }

        if ($condition['admin-role']) {
            $whereRoleAdmin = Pi::db()->where()->create(array(
                'admin.role'    => $condition['admin-role'],
                'front.section' => 'front',
            ));
            $where->add($whereRoleAdmin);
        }

        if ($condition['admin-role']) {
            $select->join(
                array('front' => $modelRole->getTable()),
                'front.uid=account.id',
                array()
            );
        }

        if ($condition['front-role']) {
            $select->join(
                array('admin' => $modelRole->getTable()),
                'admin.uid=account.id',
                array()
            );
        }

        $select->where($where);
        $rowset = Pi::db()->query($select)->current();

        return (int) $rowset['count'];
    }

    public function testAction()
    {
        $this->view()->setTemplate(false);

        $modelAccount = Pi::model('user_account');
        $modelRole = Pi::model('user_role');


        $whereRoleAdmin = Pi::db()->where()->create(array(
            'admin.role'     => 'staff',
            'admin.section'  => 'admin',
        ));

        $whereRoleFront = Pi::db()->where()->create(array(
            'front.role'     => 'member',
            'front.section'  => 'front',
        ));

        $where = Pi::db()->where();
        $where->add(array('account.active' => 1))
            ->add($whereRoleAdmin)
            ->add($whereRoleFront);

        $select = Pi::db()->select();
        $select->from(
            array('account' => $modelAccount->getTable()),
            array('id')
        );
        //$select->columns(array('id'));
        $select->join(
            array('front' => $modelRole->getTable()),
            'front.uid=account.id',
            array()
        );
        $select->join(
            array('admin' => $modelRole->getTable()),
            'admin.uid=account.id',
            array()
        );
        $select->where($where);
        $rowset = Pi::db()->query($select);

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
}