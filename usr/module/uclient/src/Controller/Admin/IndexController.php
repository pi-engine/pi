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

        // Get user ids
        $users  = $this->getUsers($condition, $limit, $offset);

        // Get user count
        $count = $this->getCount($condition);

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

        // Get user ids
        $users  = $this->getUsersByRole($condition, $limit, $offset);

        // Get user count
        $count = $this->getCountByRole($condition);

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
     * Get user information
     *
     * @param int[] $uids
     *
     * @return array
     */
    protected function getUser($uids)
    {
        $users = array();
        if (!$uids) {
            return $users;
        }

        $columns = array(
            'identity'       => '',
            'name'           => '',
            'email'          => '',
            'ip_register'    => '',
            'id'             => '',
        );

        // Get user data
        $users = Pi::service('user')->get(
            $uids,
            array_keys($columns)
        );

        $rowset = Pi::model('user_role')->select(array('uid' => $uids));
        foreach ($rowset as $row) {
            $uid     = $row['uid'];
            $section = $row['section'];
            $roleKey = $section . '_roles';
            $users[$uid][$roleKey][] = $row['role'];
        }

        return $users;
    }

    /**
     * Get users according to condition
     *
     * @param array $condition
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    protected function getUsers(array $condition, $limit = 0, $offset = 0)
    {
        $modelAccount = Pi::model('user_account');

        if ($condition['uid']) {
            $where['id'] = (int) $condition['uid'];
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

        $whereAccount = Pi::db()->where()->create($where);
        $where = Pi::db()->where();
        $where->add($whereAccount);

        $select = Pi::db()->select();
        $select->from(
            array('account' => $modelAccount->getTable()),
            array('id')
        );

        if ($condition['ip_register']) {
            $profileModel = $this->getModel('profile');
            $whereProfile = Pi::db()->where()->create(array(
                'profile.ip_register like ?' => '%' . $condition['ip_register'] . '%',
            ));
            $where->add($whereProfile);
            $select->join(
                array('profile' => $profileModel->getTable()),
                'profile.uid=account.id',
                array()
            );
        }

        $select->order('account.time_created DESC');
        if ($limit) {
            $select->limit($limit);
        }
        if ($offset) {
            $select->offset($offset);
        }

        $select->where($where);

        $rowset = Pi::db()->query($select);

        $result = array();
        foreach ($rowset as $row) {
            $result1[] = $row;
            $result[] = (int) $row['id'];
        }

        return $result;
    }

    /**
     * Get count according to condition
     *
     * @param array $condition
     *
     * @return int
     */
    protected function getCount(array $condition)
    {
        $modelAccount = Pi::model('user_account');

        $where = array();
        if ($condition['uid']) {
            $where['id'] = (int) $condition['uid'];
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

        if ($condition['ip_register']) {
            $profileModel = $this->getModel('profile');
            $whereProfile = Pi::db()->where()->create(array(
                'profile.ip_register like ?' => '%' . $condition['ip_register'] . '%',
            ));
            $where->add($whereProfile);
            $select->join(
                array('profile' => $profileModel->getTable()),
                'profile.uid=account.id',
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
     * Get user ids according to roles
     *
     * @param array $condition
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    protected function getUsersByRole(array $condition, $limit = 0, $offset = 0)
    {
        $select = Pi::model('user_role')->select();
        $select->columns(array(Pi::db()->expression('DISTINCT uid')));
        $select->where($condition)->limit($limit)->offset($offset)->order('uid DESC');
        $rowset = Pi::model('user_role')->selectWith($select);

        $uids = array();
        foreach ($rowset as $row) {
            $uids[] = $row['uid'];
        }

        return $uids;

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