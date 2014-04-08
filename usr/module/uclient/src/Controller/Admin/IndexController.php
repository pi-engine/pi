<?php
/**
* Pi Engine (http://pialog.org)
*
* @link            http://code.pialog.org for the Pi Engine source repository
* @copyright       Copyright (c) Pi Engine http://pialog.org
* @license         http://pialog.org/license.txt BSD 3-Clause License
*/

namespace Module\Uclient\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Zend\Db\Sql\Predicate;

/**
 * User manage cases controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class IndexController extends ActionController
{
    /**
     * Default action
     *
     * @return array|void
     */
    public function indexAction()
    {
        $this->view()->setTemplate('ng');
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
        $order  = 'id DESC';

        $condition = array();
        $condition['identity']  = _get('identity') ?: '';
        $condition['name']      = _get('name') ?: '';
        $condition['email']     = _get('email') ?: '';
        $condition = array_filter($condition);

        $fields = array('id', 'identity', 'name', 'email', 'time_created');

        // Get user count
        $count = Pi::service('user')->getCount($condition);

        // Get users
        if ($count) {
            $users  = Pi::service('user')->getList(
                $condition,
                $limit,
                $offset,
                $order,
                $fields
            );
            $users = $this->renderRole($users);
        } else {
            $users = array();
        }
        array_walk($users, function (&$user) {
            if ($user['time_created']) {
                $user['time_created'] = _date($user['time_created']);
            }
        });

        // Set paginator
        $paginator = array(
            'count'      => $count,
            'limit'      => $limit,
            'page'       => $page,
        );

        $data = array(
            'users'     => array_values($users),
            'paginator' => $paginator,
        );

        return $data;
    }

    /**
     * User list by role
     *
     * @return array|void
     */
    public function roleAction()
    {
        $page   = (int) $this->params('p', 1);
        $limit  = 10;
        $offset = (int) ($page -1) * $limit;
        $order  = 'id DESC';

        $condition = array();
        $condition['front_role']  = _get('front_role') ?: '';
        $condition['admin_role']  = _get('admin_role') ?: '';
        $fields = array('id', 'identity', 'name', 'email', 'time_created');

        $roles = array(
            'front' => $condition['front_role'],
            'admin' => $condition['admin_role'],
        );
        $data = $this->queryByRole(
            $roles,
            $limit,
            $offset,
            $order,
            $fields
        );
        /*
        // Get user count
        $count = $this->getCountByRole($roles);

        // Get users
        if ($count) {
            $users  = $this->getUsersByRole(
                $roles,
                $limit,
                $offset,
                '',
                $fields
            );
            $users = $this->renderRole($users);
        } else {
            $users = array();
            $message = _a('No user available.');
        }
        */

        array_walk($data['users'], function (&$user) use ($fields) {
            foreach ($fields as $field) {
                if (!isset($user[$field])) {
                    $user[$field] = null;
                }
            }
            if ($user['time_created']) {
                $user['time_created'] = _date($user['time_created']);
            }
        });

        // Set paginator
        $paginator = array(
            'count'      => $data['count'],
            'limit'      => $limit,
            'page'       => $page,
        );

        return array(
            'users'     => array_values($data['users']),
            'paginator' => $paginator
        );
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
        if ($type == 'add') {
            foreach ($uids as $uid) {
                $status = Pi::service('user')->setRole($uid, $role);
                if (!$status) {
                    $result['message'] = _a('Role assignment failed.');
                    return $result;
                }
            }
            $this->setAccount($uids, $role);

        // Remove user role
        } elseif ($type == 'remove') {
            foreach ($uids as $uid) {
                $status = Pi::service('user')->revokeRole($uid, $role);
                if (!$status) {
                    $result['message'] = _a('Role assignment failed.');
                    return $result;
                }
            }
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
     * Render roles for users
     */
    protected function renderRole(array $users)
    {
        foreach ($users as $key => $user) {
            $uids[] = $user['id'];
        }
        $roleList = array();
        $roles = Pi::registry('role')->read();
        $rowset = Pi::model('user_role')->select(array('uid' => $uids));
        foreach ($rowset as $row) {
            $uid     = $row['uid'];
            $section = $row['section'];
            $roleKey = $section . '_roles';
            $roleList[$uid][$roleKey][] = $roles[$row['role']]['title'];
        }
        array_walk($users, function (&$user) use ($roleList) {
            $uid = $user['id'];
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
     * Get users according to roles
     *
     * @param array $roles
     * @param int $limit
     * @param int $offset
     * @param string|array $order
     * @param array $fields
     *
     * @return array
     */
    protected function queryByRole(
        array $roles,
        $limit = 0,
        $offset = 0,
        $order = '',
        $fields = array()
    ) {
        $frontRoles = Pi::registry('role')->read('front');
        $adminRoles = Pi::registry('role')->read('admin');
        unset($frontRoles['guest']);
        $rolesList = array(
            'front' => array_keys($frontRoles),
            'admin' => array_keys($adminRoles),
        );

        if ($roles['front'] && $roles['admin']) {
            $isJoin = true;
        } else {
            $isJoin = false;
        }
        //$isJoin = false;
        $where = Pi::db()->where();
        foreach (array('front', 'admin') as $section) {
            $key = $isJoin ? $section . '.role' : 'role';
            if ('none_' . $section == $roles[$section]) {
                $where->notIn($key, $rolesList[$section]);
            } elseif ('any_' . $section == $roles[$section]) {
                $where->in($key, $rolesList[$section]);
            } elseif ($roles[$section]) {
                $where->equalTo($key, $roles[$section]);
            }
        }
        $model = Pi::model('user_role');
        if ($isJoin) {
            $select = Pi::db()->select();
            $select->from(array('front' => $model->getTable()));
            $select->columns(array(
                'count' => Pi::db()->expression('COUNT(DISTINCT front.uid)')
            ));
            $select->join(
                array('admin' => $model->getTable()),
                'front.uid=admin.uid',
                array()
            );
            $select->where($where);
            $row = Pi::db()->query($select)->current();
        } else {
            $select = $model->select();
            $select->columns(array(
                'count' => Pi::db()->expression('COUNT(DISTINCT uid)')
            ));
            $select->where($where);
            $row = $model->selectWith($select)->current();
        }
        $count = (int) $row['count'];
        $users = array();
        if ($count) {
            if ($isJoin) {
                $select = Pi::db()->select();
                $select->where($where);
                $select->join(
                    array('admin' => $model->getTable()),
                    'front.uid=admin.uid',
                    array()
                );
                $select->from(array('front' => $model->getTable()));
                $select->columns(array('uid'));
                $select->group('front.uid');
                $select->limit($limit)->offset($offset);
                $order = $order ?: 'front.id DESC';
                $select->order($order);
                $rowset = Pi::db()->query($select);
            } else {
                $select = $model->select();
                $select->where($where);
                //$select->columns(array('uid' => Pi::db()->expression('DISTINCT uid')));
                $select->columns(array('uid'));
                $select->group('uid');
                $select->limit($limit)->offset($offset);
                $order = $order ?: 'id DESC';
                $select->order($order);
                $rowset = $model->selectWith($select);
            }
            $uids = array();
            foreach ($rowset as $row) {
                $uids[] = (int) $row['uid'];
            }
            $users = Pi::service('user')->mget($uids, $fields);
            foreach ($uids as $uid) {
                if (!isset($users[$uid])) {
                    $users[$uid] = array(
                        'id'    => $uid,
                    );
                }
            }
            $users = $this->renderRole($users);
        }

        $result = array(
            'count' => $count,
            'users' => $users,
        );

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
                'section'  => $role['section'],
            );
        }

        return $data;
    }

    /**
     * Build local user accounts
     *
     * @param int[] $uids
     * @param string $role
     *
     * @return void
     */
    protected function setAccount($uids, $role)
    {
        $roles = Pi::registry('role')->read('admin');
        if (!isset($roles[$role])) {
            return;
        }
        $where  = Pi::db()->where(array('id' => $uids));
        $ids    = Pi::api('user', 'system')->getUids($where);
        $newIds = array_diff($uids, $ids);
        if ($newIds) {
            $users  = Pi::service('user')->mget(
                $newIds,
                array('id', 'identity', 'email')
            );
            $model = Pi::model('user_account');
            foreach ($users as $uid => $user) {
                $row = $model->find($uid);
                if ($row) {
                    $row->assign(array(
                        'identity' => $user['identity'],
                        'email'    => $user['email'],
                    ));
                } else {
                    $row = $model->createRow(array(
                        'id'             => $uid,
                        'identity'       => $user['identity'],
                        'email'          => $user['email'],
                        'active'         => 1,
                        //'time_activated' => time(),
                        //'time_created'   => time(),
                        'credential'     => md5(uniqid(mt_rand(), true)),
                    ));
                    $row->prepare();
                }
                $row->save();
            }
        }
    }
}
