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
     *
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

        $condition = array();
        $condition['name']          = _get('name') ?: '';
        $condition['email']         = _get('email') ?: '';
        $fields = array('id', 'identity', 'name', 'email', 'time_created');

        // Get user count
        $count = Pi::service('user')->getCount($condition);

        // Get users
        if ($count) {
            $users  = Pi::service('user')->getList(
                $condition,
                $limit,
                $offset,
                '',
                $fields
            );
            $users = $this->renderRole($users);
        } else {
            $users = array();
        }

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
     * User list by role
     *
     * @return array|void
     */
    public function roleAction()
    {
        $page   = (int) $this->params('p', 1);
        $limit  = 10;
        $offset = (int) ($page -1) * $limit;

        $condition      = array();
        $condition[]    = _get('front_role') ?: '';
        $condition[]    = _get('admin_role') ?: '';
        $condition = array_filter($condition);
        $fields = array('id', 'identity', 'name', 'email', 'time_created');

        // Get user count
        $count = $this->getCountByRole($condition);

        // Get users
        if ($count) {
            $users  = $this->getUsersByRole(
                $condition,
                $limit,
                $offset,
                '',
                $fields
            );
            $users = $this->renderRole($users);
        } else {
            $users = array();
        }

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
                $status = Pi::service('user')->setRole($uid, $role);
                if (!$status) {
                    $result['message'] = __('Assign role failed');
                    return $result;
                }
            }
        }

        // Remove user role
        if ($type == 'remove') {
            foreach ($uids as $uid) {
                $status = Pi::service('user')->revokeRole($uid, $role);
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
     * Get user ids according to roles
     *
     * @param array $condition
     * @param int $limit
     * @param int $offset
     * @param string|array $order
     * @param array $fields
     *
     * @return array
     */
    protected function getUsersByRole(
        array $condition,
        $limit = 0,
        $offset = 0,
        $order = '',
        $fields = array()
    ) {
        $order = $order ?: 'uid DESC';
        $select = Pi::model('user_role')->select();
        //$select->columns(array(Pi::db()->expression('DISTINCT uid')));
        $select->columns(array('uid'));
        $select->group('uid');
        $select->where($condition)->limit($limit)->offset($offset)->order($order);
        $rowset = Pi::model('user_role')->selectWith($select);

        $uids = array();
        foreach ($rowset as $row) {
            $uids[] = $row['uid'];
        }

        $result = Pi::service('user')->get($uids, $fields);

        return $result;
    }

    /**
     * Get user account according to roles
     *
     * @param array $condition
     *
     * @return int
     */
    protected function getCountByRole(array $condition)
    {
        $select = Pi::model('user_role')->select();
        $select->columns(array(
            'count' => Pi::db()->expression('COUNT(DISTINCT uid)')
        ));
        $select->where($condition);
        $row = Pi::model('user_role')->selectWith($select)->current();
        $result = (int) $row['count'];

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
                'type'  => $role['section'],
            );
        }

        return $data;
    }
}