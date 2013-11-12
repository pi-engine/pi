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
use Pi\User\Model\Client as UserModel;
use Pi\Mvc\Controller\ActionController;
use Pi\Paginator\Paginator;

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

        $roles = Pi::registry('role')->read();
        $model = Pi::model('user_role');
        $message = '';
        if ($op && $name) {
            if ('uid' == $field) {
                $name   = (int) $name;
                $field  = 'id';
            }
            $user = Pi::service('user')->getUser($name, $field);
            $uid = (int) $user->get('id');
            if ($uid) {
                $data = array('role' => $role, 'uid' => $uid);
                $count = $model->count($data);
                if ('remove' == $op) {
                    if ($count) {
                        $status = 1;
                        $model->delete($data);
                        $message = _a('User removed from the role.');
                        $data = array('id' => $uid);
                    } else {
                        $status = 0;
                        $message = _a('User not in the role.');
                        $data = array('id' => $uid);
                    }
                } else {
                    if (!$count) {
                        $status = 1;
                        $data['section'] = $roles[$role]['section'];
                        $row = $model->createRow($data);
                        $row->save();
                        $message = _a('User added to the role.');
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
                        $message = _a('User already in the role.');
                        $data = array('uid' => $uid);
                    }
                }
                if (1 == $status && 'remove' != $op) {
                    $this->setAccount($user, $role);
                }
            } else {
                $status = 0;
                $message = _a('User not found.');
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
        $users = Pi::service('user')->mget($uids, array('uid', 'name'));
        $avatars = Pi::service('avatar')->getList($uids, 'small');
        array_walk($users, function (&$user) use ($avatars) {
            //$user['avatar'] = $avatars[$uid];
            $user['url'] = Pi::service('user')->getUrl('profile', $user['id']);
        });
        $count = $model->count(array('role' => $role));
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

        $title = sprintf(_a('Users of role %s'), $roles[$role]['title']);
        
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
    }

    /**
     * Build local user account
     *
     * @param UserModel|int $user
     * @param string $role
     *
     * @return void
     */
    protected function setAccount($user, $role)
    {
        $roles = Pi::registry('role')->read('admin');
        if (!isset($roles[$role])) {
            return;
        }
        if ($user instanceof UserModel) {
            $uid = $user->get('id');
        } else {
            $uid = (int) $user;
            $user = Pi::service('user')->getUser($uid);
        }
        $model = Pi::model('user_account');
        $row = $model->find($uid);
        if ($row) {
            return;
        }
        $row = $model->createRow(array(
            'id'            => $uid,
            'identity'      => $user->get('identity'),
            'credential'    => md5(uniqid(mt_rand(), true)),
        ));
        $row->prepare();
        $row->save();
    }
}
