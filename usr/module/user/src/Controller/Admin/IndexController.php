<?php
/**
* Pi Engine (http://pialog.org)
*
* @link            http://code.pialog.org for the Pi Engine source repository
* @copyright       Copyright (c) Pi Engine http://pialog.org
* @license         http://pialog.org/license.txt New BSD License
*/

namespace Module\User\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;
use Pi\Acl\Acl;

/**
* User manage cases controller
*
* @author Liu Chuang <liuchuang@eefocus.com>
*/
class IndexController extends ActionController
{
    public function indexAction()
    {
        $page   = (int) $this->params('p', 1);
        $limit  = 10;
        $offset = (int) ($page -1) * $limit;

        $condition['state']        = _post('state');
        $condition['front-role']   = _post('front-role');
        $condition['admin-role']   = _post('admin-role');
        $condition['time-created'] = _post('time-created');

        // Get user ids
        $uids  = $this->getUids($condition, 'activated', $limit, $offset);
        vd($uids);exit;
        // Get user count
        $count = $this->getCount($condition);


        // Get user ids

        // Get user information
        $users = $this->getUser($uids, 'activated');

        // Set paginator
        $paginatorOption = array(
            'count'      => $count,
            'limit'      => $limit,
            'page'       => $page,
            'controller' => 'index',
            'action'     => 'index',
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

        $condition = array(
            'time_activated' => 0,
        );
        $uids = Pi::api('user', 'user')->getUids($condition, $limit, $offset);
        $count = Pi::api('user', 'user')->getCount($condition);
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

                // Get register ip
                // TO DO

                // Get login time
                // TO DO
            }
            $return = $users;
        }
        return $return;
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
            $options = array(
                '' => __('Front role')
            );

            $rowset = $model->select(array('section' => 'front'));
            foreach ($rowset as $row) {
                $options[$row->name] = __($row->title);
            }
        }
        if ($section == 'admin') {
            // Get admin role
            $options = array(
                '' => __('Admin role'),
            );

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
        $paginator = Paginator::factory(intval($option['count']));
        $paginator->setItemCountPerPage($option['limit']);
        $paginator->setCurrentPageNumber($option['page']);
        $paginator->setUrlOptions(array(
            // Use router to build URL for each page
            'pageParam'     => 'p',
            'totalParam'    => 't',
            'router'        => $this->getEvent()->getRouter(),
            'route'         => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
            'params'        => array(
                'module'        => $this->getModule(),
                'controller'    => $option['controller'],
                'action'        => $option['action'],
                'uid'           => $option['uid'],
            ),
        ));

        return $paginator;
    }

    protected function getUids($condition, $type, $limit = 0, $offset = 0)
    {
        $uids = array();

        $modelAccount = $this->getModel('account');

        $select = Pi::db()->select();
        $select->from(array('account' => $modelAccount->getTable()));
        $select->columns(array('id'));

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

        $select = $accountModel->select()->where($where);
        $select->columns(array('id'));
        $rowset = $accountModel->selectWith($select);

        foreach ($rowset as $row) {
            $uids[] = $row->id;
        }

        if (empty($uids)) {
            return $uids;
        }

        $roleModel = Pi::model('user_role');
        $where     = array(
            'uid' => $uids,
        );

        // Search front role
        if ($condition['front-role']) {
            $where['role'] = $condition['front-role'];
            $select = $roleModel->select()->where($where);
            $select->columns(array('uid'));
            $rowset = $roleModel->selectWith($select);
            $uids = array();
            foreach ($rowset as $row) {
                $uids[] = $row->id;
            }

            if (empty($uids)) {
                return $uids;
            }
        }



        return $uids;

    }

    protected function getCount($condition)
    {
        $count = 0;

        return $count;

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

        foreach ($rowset as $row) {
            $result[] = (int) $row['id'];
        }

        vd($result);
        //vd($this->getUids());
       // $model = Pi::model('user_role');
        //$seletc = $model->select()->where('("role" = "admin" and )');


//        $k = date("Y-m-d",mktime(0,0,0,date("m"),date("d"),date("Y")));
//        $k = date("Y-m-d",mktime(0,0,0,date("m")-3,date("d"),date("Y")));
//        vd($k);
    }
}