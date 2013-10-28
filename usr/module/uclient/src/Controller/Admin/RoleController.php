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
        array_walk($roles, function (&$role, $name) use ($count) {
            $role['name']   = $name;
            $role['count']  = isset($count[$name]) ? (int) $count[$name] : 0;
        });

        return array(
            'roles'    => array_values($roles)
        );
    }

    /**
     * Users of a role
     */
    public function userAction()
    {
        $role   = $this->params('name', 'member');
        // Operation: add, remove
        $op     = $this->params('op');
        // User value
        $name   = $this->params('user');
        // User value field: uid, identity, name, email
        $field  = $this->params('field', 'uid');

        $model = Pi::model('user_role');
        $message = '';
        if ($op && $name) {
            if ('uid' == $field) {
                $uid = (int) $name;
            } else {
                $user = Pi::service('user')->getUser($name, $field);
                if ($user) {
                    $uid = $user->get('id');
                } else {
                    $uid = 0;
                }
            }
            if ($uid) {
                $data = array('role' => $role, 'uid' => $uid);
                $count = $model->count($data);
                if ('remove' == $op) {
                    if ($count) {
                        $status = 1;
                        $model->delete($data);
                        $message = __('User removed from the role.');
                        $data = array('id' => $uid);
                    } else {
                        $status = 0;
                        $message = __('User not in the role.');
                        $data = array('id' => $uid);
                    }
                } else {
                    if (!$count) {
                        $status = 1;
                        $row = $model->createRow($data);
                        $row->save();
                        $message = __('User added to the role.');
                        $data = array(
                            'id'    => $uid,
                            'name'  => Pi::service('user')->get($uid, 'name'),
                            'url'   => Pi::service('user')->getUrl(
                                'profile',
                                $uid
                            ),
                        );
                    } else {
                        $status = 0;
                        $message = __('User already in the role.');
                        $data = array('uid' => $uid);
                    }
                }
            } else {
                $status = 0;
                $message = __('User not found.');
                $data = array('id' => $uid);
            }

            return compact('status', 'message', 'data');
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
        array_walk($users, function (&$user) use ($avatars) {
            //$user['avatar'] = $avatars[$uid];
            $user['url'] = Pi::service('user')->getUrl('profile', $user['id']);
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
        
        $paginator = array(
            'page'    => $page,
            'count'   => $count,
            'limit'   => $limit
        );
       
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
