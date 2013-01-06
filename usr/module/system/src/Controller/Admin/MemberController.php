<?php
/**
 * Member management controller
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Module\System
 * @version         $Id$
 */

namespace Module\System\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\System\Form\MemberForm;
use Module\System\Form\MemberFilter;
use Module\System\Form\PasswordForm;
use Module\System\Form\PasswordFilter;
use Zend\Db\Sql\Predicate\Expression;
use Pi\Paginator\Paginator;

/**
 * Feature list:
 * 1. Member list
 * 2. Member account create
 * 2. Member account/profile edit
 * 3. Memeber delete
 */
class MemberController extends ActionController
{
    protected $columns = array(
        'name', 'identity', 'email'
    );

    protected function getRoles()
    {
        $roles = array(
            ''  => __('All'),
        );
        $model = Pi::model('acl_role');
        $rowset = $model->select(array());
        foreach ($rowset as $row) {
            $roles[$row->name] = __($row->title);
        }

        return $roles;
    }

    public function indexAction()
    {
        //$role = $this->params('role', null);
        $active = $this->params('active', null);
        $page = $this->params('p', 1);
        $limit = 50;
        $offset = (int) ($page - 1) * $limit;

        $roles = $this->getRoles();

        $model = Pi::model('user');
        $where = array();
        if (null !== $active) {
            $where['active'] = (int) $active;
        }
        $select = $model->select()->where($where)->order('id')->offset($offset)->limit($limit);
        $rowset = $model->selectWith($select);
        $users = array();
        foreach ($rowset as $row) {
            $users[$row->id] = array(
                'id'        => $row->id,
                'identity'  => $row->identity,
                'name'      => $row->name,
                'email'     => $row->email,
                'active'    => $row->active,
            );
        }
        $select = $model->select()->columns(array('count' => new Expression('count(*)')))->where($where);
        $count = $model->selectWith($select)->current()->count;

        $roleList = array();
        $model = Pi::model('user_role');
        $rowset = $model->select(array('user' => array_keys($users)));
        foreach ($rowset as $row) {
            $users[$row->user]['role'] = $row->role;
            $roleList[$row->role] = '';
        }

        $model = Pi::model('user_staff');
        $rowset = $model->select(array('user' => array_keys($users)));
        foreach ($rowset as $row) {
            $users[$row->user]['role_staff'] = $row->role;
            $roleList[$row->role] = '';
        }

        foreach (array_keys($roleList) as $name) {
            $roleList[$name] = $roles[$name];
        }
        /*
        $model = Pi::model('acl_role');
        $rowset = $model->select(array('name' => array_keys($roleList)));
        foreach ($rowset as $row) {
            $roleList[$row->name] = __($row->title);
        }
        */
        foreach ($users as $id => &$user) {
            $user['role'] = $roleList[$user['role']];
            $user['role_staff'] = $roleList[$user['role_staff']];
        }

        $paginator = Paginator::factory(intval($count));
        $paginator->setItemCountPerPage($limit);
        $paginator->setCurrentPageNumber($page);
        $paginator->setUrlOptions(array(
            // Use router to build URL for each page
            'pageParam'     => 'p',
            'totalParam'    => 't',
            'router'        => $this->getEvent()->getRouter(),
            'route'         => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
            'params'        => array(
                'module'        => $this->getModule(),
                'controller'    => 'member',
                'action'        => 'index',
                //'role'          => $role,
            ),
        ));
        $this->view()->assign('paginator', $paginator);

        $title = __('Member list');
        $this->view()->assign('title', $title);
        $this->view()->assign('users', $users);
        $this->view()->assign('role', $role);
        $this->view()->assign('roles', $roles);
    }

    public function roleAction()
    {
        $role = $this->params('role', null);
        if (!$role) {
            $this->redirect()->toRoute('', array('action' => 'index'));
            return;
        }

        $active = $this->params('active', null);
        $page = $this->params('p', 1);
        $limit = 50;
        $offset = (int) ($page - 1) * $limit;

        $roles = $this->getRoles();

        $row = Pi::model('acl_role')->find($role, 'name');
        $roleTitle = __($row->title);

        $isFront = true;
        if ('front' == $row->section) {
            $model = Pi::model('user_role');
        } else {
            $model = Pi::model('user_staff');
            $isFront = false;
        }
        $where = array();
        if (null !== $role) {
            $where['role'] = $role;
        }
        $select = $model->select()->where($where)->order('user')->offset($offset)->limit($limit);
        $rowset = $model->selectWith($select);
        $users = array();
        foreach ($rowset as $row) {
            $users[$row->user] = array(
                'role'  => $row->role,
            );
        }
        $select = $model->select()->columns(array('count' => new Expression('count(*)')))->where($where);
        $count = $model->selectWith($select)->current()->count;

        if ($users) {
            $roleList = array();
            $rowset = Pi::model('user')->select(array('id' => array_keys($users)));
            $roleType = $isFront ? 'role' : 'role_staff';
            foreach ($rowset as $row) {
                $userRole = $users[$row->id]['role'];
                $users[$row->id] = array(
                    'id'        => $row->id,
                    'identity'  => $row->identity,
                    'name'      => $row->name,
                    'email'     => $row->email,
                    'active'    => $row->active,
                    $roleType   => $userRole,
                );
                $roleList[$userRole] = '';
            }

            $model = $isFront ? Pi::model('user_staff') : Pi::model('user_role');
            $rowset = $model->select(array('user' => array_keys($users)));
            $roleType = $isFront ? 'role_staff' : 'role';
            foreach ($rowset as $row) {
                $users[$row->user][$roleType] = $row->role;
                $roleList[$row->role] = '';
            }

            foreach (array_keys($roleList) as $name) {
                $roleList[$name] = $roles[$name];
            }

            foreach ($users as $id => &$user) {
                $user['role'] = $roleList[$user['role']];
                $user['role_staff'] = $roleList[$user['role_staff']];
            }
        }

        $paginator = Paginator::factory(intval($count));
        $paginator->setItemCountPerPage($limit);
        $paginator->setCurrentPageNumber($page);
        $paginator->setUrlOptions(array(
            // Use router to build URL for each page
            'pageParam'     => 'p',
            'totalParam'    => 't',
            'router'        => $this->getEvent()->getRouter(),
            'route'         => $this->getEvent()->getRouteMatch()->getMatchedRouteName(),
            'params'        => array(
                'module'        => $this->getModule(),
                'controller'    => 'member',
                'action'        => 'role',
                'role'          => $role,
            ),
        ));
        $this->view()->assign('paginator', $paginator);

        $title = sprintf(__('Member list of role "%s"'), $roleTitle);
        $this->view()->assign('title', $title);

        $this->view()->assign('users', $users);
        $this->view()->assign('role', $role);
        $this->view()->assign('roles', $roles);
        $this->view()->setTemplate('member-index');
    }

    public function addAction()
    {
        $form = new MemberForm('member');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new MemberFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                $result = Pi::service('api')->system(array('member', 'add'), $values);
                if ($result['status']) {
                    $message = __('User created saved successfully.');
                    $this->jump(array('action' => 'index'));
                    return;
                } else {
                    $message = __('User not created.');
                }
            } else {
                $message = $form->getMessage() ?: __('Invalid data, please check and re-submit.');
            }
        } else {
            $message = '';
        }

        $title = __('Create user');
        $this->view()->assign(array(
            'title'     => $title,
            'form'      => $form,
            'message'   => $message,
        ));
    }

    public function editAction()
    {
        $id = $this->params('id');
        $row = Pi::model('user')->find($id);
        if (!$row) {
            $this->jump(array('action' => 'index'), __('The user is not found.'));
        }
        $user = $row->toArray();
        $role = Pi::model('user_role')->find($row->id, 'user');
        if ($role) {
            $user['role'] = $role->role;
        }
        $roleStaff = Pi::model('user_staff')->find($row->id, 'user');
        if ($roleStaff) {
            $user['role_staff'] = $roleStaff->role;
        }

        $form = new MemberForm('member', $user);
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $filter = new MemberFilter;
            $filter->remove('credential')->remove('credential-confirm');
            $form->setInputFilter($filter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                $result = Pi::service('api')->system(array('member', 'update'), $values);
                if ($result['status']) {
                    $message = __('User data saved successfully.');
                    $this->jump(array('action' => 'index'));
                    return;
                } else {
                    $message = __('User data not saved.');
                }
            } else {
                $message = $form->getMessage() ?: __('Invalid data, please check and re-submit.');
            }
        } else {
            $message = '';
        }

        $title = __('Edit member');
        $this->view()->assign(array(
            'title'     => $title,
            'form'      => $form,
            'message'   => $message,
        ));
    }

    public function passwordAction()
    {
        $id = $this->params('id');
        $row = Pi::model('user')->find($id);
        if (!$row) {
            $this->jump(array('action' => 'index'), __('The user is not found.'));
        }

        $form = new PasswordForm('password');
        $form->remove('credential')->setData(array('identity' => $row->identity));
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $filter = new PasswordFilter;
            $filter->remove('credential');
            $form->setInputFilter($filter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                $row->credential = $values['credential-new'];
                try {
                    $row->prepare()->save();
                    $status = true;
                } catch (\Exception $e) {
                    $status = false;
                }
                if ($status) {
                    $message = __('User password saved successfully.');

                    $this->jump(array('action' => 'index'));
                    return;
                } else {
                    $message = __('User password not saved.');
                }
            } else {
                $message = __('Invalid data, please check and re-submit.');
            }
        } else {
            $message = '';
        }

        $title = __('Change member password');
        $this->view()->assign(array(
            'title'     => $title,
            'form'      => $form,
            'message'   => $message,
        ));
    }

    public function deleteAction()
    {
        $id = $this->params('id');
        if ($id == 1) {
            $this->jump(array('action' => 'index'), __('The user is protected from deletion.'));
            return;
        }
        Pi::service('api')->system(array('member', 'delete'), $id);

        $this->jump(array('action' => 'index'), __('The user is deleted successfully.'));
        $this->setTemplate('false');
    }
}
