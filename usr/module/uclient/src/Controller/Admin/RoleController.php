<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Uclient\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;
use Module\User\Form\RoleForm;
use Module\User\Form\RoleFilter;

/**
 * Role controller
 *
 * Feature list:
 *
 *  1. List of roles with inheritance
 *  2. User list of a role
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class RoleController extends ActionController
{
    /**
     * Get role list
     *
     * @return array
     */
    protected function getRoles()
    {
        $roles = Pi::registry('role')->read();
        if (isset($roles['guest'])) {
            unset($roles['guest']);
        }

        return $roles;
    }

    /**
     * Entrance template
     *
     * @return void
     */
    public function indexAction()
    {
        $this->view()->setTemplate('role');
    }

    /**
     * List of roles
     */
    public function listAction()
    {
        $roles = $this->getRoles();
        $rowset = Pi::model('user_role')->count(
            array('role' => array_keys($roles)),
            'role'
        );
        $count = array();
        foreach ($rowset as $row) {
            $count[$row['role']] = (int) $row['count'];
        }

        $frontRoles = array();
        $adminRoles = array();
        foreach ($roles as $name => $role) {
            $role['name'] = $name;
            $role['count'] = isset($count[$name])
                ? (int) $count[$name] : 0;
            if ('admin' == $role['section']) {
                $adminRoles[] = $role;
            } else {
                $frontRoles[] = $role;
            }
        }

        return array(
            'frontRoles'    => $frontRoles,
            'adminRoles'    => $adminRoles,
        );
    }

    /**
     * Users of a role
     */
    public function userAction()
    {
        $role   = $this->params('name', 'member');
        $op     = $this->params('op');
        $uid    = $this->params('uid');

        $model = Pi::model('user_role');
        $message = '';
        if ($op && $uid) {
            if (is_numeric($uid)) {
                $uid = (int) $uid;
            } else {
                $user = Pi::service('user')->getUser($uid, 'name');
                if ($user) {
                    $uid = $user->get('id');
                } else {
                    $uid = 0;
                }
            }
            if ($uid) {
                $data = array('role' => $role, 'uid' => $uid);
                $count = $model->count($data);
                if ('remove' == $op && $count) {
                    $model->delete($data);
                    $message = __('User removed.');
                    $data = array('uid' => $uid);
                } elseif ('add' == $op && !$count) {
                    $row = $model->createRow($data);
                    $row->save();
                    $message = __('User added.');
                    $data = array(
                        'uid'   => $uid,
                        'name'  => Pi::service('user')->get($uid, 'name')
                    );
                }

                return array(
                    'status'    => 1,
                    'message'   => $message,
                    'data'      => $data,
                );
            }
        }

        $page   = _get('page', 'int') ?: 1;
        $limit  = 20;
        $offset = ($page - 1) * $limit;

        $select = $model->select();
        $select->where(array('role' => $role))->limit(20)->offset($offset);
        $rowset = $model->selectWith($select);
        $uids = array();
        foreach ($rowset as $row) {
            $uids[] = (int) $row['uid'];
        }
        $users = Pi::service('user')->get($uids, array('uid', 'name'));
        $avatars = Pi::service('avatar')->getList($uids, 'small');
        array_walk($users, function (&$user, $uid) use ($avatars) {
            //$user['avatar'] = $avatars[$uid];
            $user['url'] = Pi::service('user')->getUrl('profile', $uid);
        });
        $count = count($uids);
        if ($count >= $limit) {
            $count = $model->count(array('role' => $role));
        }

        /*
        $paginator = Paginator::factory($count, array(
            'page'          => $page,
            'url_options'   => array(
                'params'    => array('role' => $role),
            ),
        ));
        */
        $roles = Pi::registry('role')->read();
        $title = sprintf(__('Users of role %s'), $roles[$role]['title']);
        if ($count > $limit) {
            $paginator = array(
                'page'    => $page,
                'count'   => $count,
                'limit'   => $limit
            );
        } else {
            $paginator = array();
        }

        $data = array(
            'title'     => $title,
            'users'     => array_values($users),
            'paginator' => $paginator,
        );

        return $data;
        /*
        $this->view()->assign(array(
            'title'     => $title,
            'role'      => $role,
            'count'     => $count,
            'users'     => $users,
            'message'   => $message,
            'paginator' => $paginator,
        ));

        $this->view()->setTemplate('role-user');
        */
    }

}
