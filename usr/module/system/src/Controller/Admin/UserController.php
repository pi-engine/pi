<?php
/**
* Pi Engine (http://pialog.org)
*
* @link            http://code.pialog.org for the Pi Engine source repository
* @copyright       Copyright (c) Pi Engine http://pialog.org
* @license         http://pialog.org/license.txt New BSD License
*/

namespace Module\System\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Zend\Db\Sql\Predicate;


/**
 * User manage cases controller
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class UserController extends ActionController
{
    /**
     * Default action
     *
     * @return array|void
     */
    public function indexAction() {
        $this->view()->setTemplate('user-index');
        $this->view()->assign(array(
            'roles'  => $this->getRoles(),
        ));
    }

    /**
     * User list
     *
     * @return array
     */
    public function listAction()
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
            if (false !== strpos($condition['search'], '@')) {
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
        $users = $this->getUser($uids);

        // Set paginator
        $paginator = array(
            'count'      => (int) $count,
            'limit'      => $limit,
            'page'       => $page,
        );

        foreach ($condition as $key => $value) {
            if ($value) {
                $params[$key] = $value;
            }
        }

        $data = array(
            'users'       => array_values($users),
            'paginator'   => $paginator,
            'condition'   => $condition,
        );

        return $data;
    }

    /**
     * Add user
     *
     * @return array
     */
    public function addUserAction()
    {
        $result = array(
            'status'  => 0,
            'message' => '',
        );

        // Get data
        $identity   = _post('identity');
        $name       = _post('name');
        $email      = _post('email');
        $credential = _post('credential');
        $activated  = (int) _post('activated');
        $enable     = (int) _post('enable');
        $role       = _post('role');

        $role = array_unique(explode(',', $role));
        // Check duplication
        $where = array(
            'identity' => $identity,
            'name'     => $name,
            'email'    => $email,
        );
        $select = Pi::model('user_account')->select()->where(
            $where,
            Predicate\PredicateSet::OP_OR
        );
        $rowset = Pi::model('user_account')->selectWith($select)->toArray();
        if (count($rowset) != 0 || empty($role)) {
            $result['message'] = __('Add user failed');
            return $result;
        }

        $data = array(
            'identity'   => $identity,
            'name'       => $name,
            'email'      => $email,
            'credential' => $credential,
        );

        // Add user
        $uid = Pi::api('system', 'user')->addUser($data, false);
        if (!$uid) {
            $result['message'] = __('Add user failed');
            return $result;
        }

        // Activate
        if ($activated == 1) {
            Pi::api('system', 'user')->activateUser($uid);
        }

        // Enable
        if ($enable == 1) {
            Pi::api('system', 'user')->enableUser($uid);
        }

        // Set role
        Pi::api('system', 'user')->setRole($uid, $role);

        $result['status']  = 1;
        $result['message'] = __('Add user sucessfully');

        return $result;

    }

    /**
     * Get user
     *
     * @return array
     */
    public function getUserAction()
    {
        $result = array();

        $uid = _get('uid');
        if (!$uid) {
            return $result;
        }

        $data = $this->getUser(array($uid));

        return $data;

    }

    /**
     * Update user
     *
     * @return array
     */
    public function updateUserAction()
    {
        $result = array(
            'status'  => 0,
            'message' => '',
        );

        // Get data
        $uid        = _post('uid');
        $identity   = _post('identity');
        $name       = _post('name');
        $email      = _post('email');
        $credential = _post('credential');
        $activated  = (int) _post('activated');
        $enable     = (int) _post('enable');
        $role       = _post('role');

        if (!$uid) {
            $result['message'] = __('Update user failed');
            return $result;
        }

        // Check uid
        $row = Pi::model('user_account')->find($uid, 'id');
        if (!$row) {
            $result['message'] = __('Update user failed');
            return $result;
        }

        $role = array_unique(explode(',', $role));
        $data = array(
            'identity' => $identity,
            'name'     => $name,
            'email'    => $email,
        );
        if ($credential) {
            $data['credential'] = $credential;
        }

        // Update account
        Pi::api('system', 'user')->updateUser($uid, $data);
        // Update role
        Pi::api('system', 'user')->setRole($uid, $role);
        // Activate
        if ($activated == 1) {
            Pi::api('system', 'user')->activateUser($uid);
        }
        // Enable or disable
        if ($enable == 1) {
            Pi::api('system', 'user')->enableUser($uid);
        } else {
            Pi::api('system', 'user')->disableUser($uid);
        }

        $result['status'] = 1;
        $result['message'] = __('Update user successfully');

        return $result;

    }

    /**
     * Check username, email, display name exist
     *
     * @return array
     */
    public function checkExistAction()
    {
        $status = 1;

        $identity = _get('identity');
        $email    = _get('email');
        $name     = _get('name');
        $uid      = (int) _get('uid');

        if (!$identity && !$email && !$name ) {
            return array(
                'status' => $status,
            );
        }

        $model = Pi::model('user_account');
        if ($identity) {
            $row = $model->find($identity, 'identity');
            if (!$row) {
                $status = 0;
            } else {
                $status = ($row['id'] == $uid) ? 0 : 1;
            }
        }

        if ($email) {
            $row = $model->find($email, 'email');
            if (!$row) {
                $status = 0;
            } else {
                $status = ($row['id'] == $uid) ? 0 : 1;
            }
        }

        if ($name) {
            $row = $model->find($name, 'name');
            if (!$row) {
                $status = 0;
            } else {
                $status = ($row['id'] == $uid) ? 0 : 1;
            }
        }

        return array(
            'status' => $status,
        );

    }

    /**
     * Get user information for list
     *
     * @param int[] $uids
     *
     * @return array
     */
    protected function getUser($uids)
    {
        if (!$uids) {
            return array();
        }
        $users = array();
        $columns = array(
            'identity'       => '',
            'name'           => '',
            'email'          => '',
            'active'         => '',
            'time_disabled'  => '',
            'time_activated' => '',
            'time_created'   => '',
            'id'             => '',
        );

        $users = Pi::api('system', 'user')->get(
            $uids,
            array_keys($columns)
        );

        $roles = Pi::registry('role')->read();
        $rowset = Pi::model('user_role')->select(array('uid' => $uids));
        foreach ($rowset as $row) {
            $uid = $row['uid'];
            $section = $row['section'];
            $roleKey = $section . '_role';
            $users[$uid][$roleKey][] = $roles[$row['role']]['title'];
        }

        foreach ($users as &$user) {
            $user['active']         = (int) $user['active'];
            $user['time_disabled']  = (int) $user['time_disabled'];
            $user['time_activated'] = (int) $user['time_activated'];
            $user['time_created']   = (int) $user['time_created'];
            $user = array_merge($columns, $user);
        }

        return $users;

    }

    /**
     * Get user ids according to condition
     *
     * @param array   $condition
     * @param int $limit
     * @param int $offset
     *
     * @return array
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
        if ($condition['activated'] == 'activated') {
            $where['time_activated > ?'] = 0;
        }
        if ($condition['pending'] == 'pending') {
            $where['time_activated'] = 0;
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
     *
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
        if ($condition['activated'] == 'activated') {
            $where['time_activated > ?'] = 0;
        }
        if ($condition['pending'] == 'pending') {
            $where['time_activated'] = 0;
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

    /**
     * Get role list
     *
     * @return array
     */
    protected function getRoles()
    {
        $roles = Pi::registry('role')->read();
        $data = array();
        foreach ($roles as $role) {
            $data[] = array(
                'name'  => $role['name'],
                'title' => $role['title'],
                'type'  => $role['section'],
            );
        }

        return $data;

    }
}