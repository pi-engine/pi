<?php
/**
 * System module member manipulation API class
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

namespace Module\System\Api;

use Pi;
use Pi\Application\AbstractApi;
use Pi\Db\RowGateway\RowGateway;

class Member extends AbstractApi
{
    protected $module = 'system';

    protected $accountColumns = array(
        'id',
        'identity', 'name', 'email', 'active', 'credential', 'salt'
    );
    protected $roleColumns = array(
        'user', 'role'
    );

    protected function canonize($user)
    {
        $account = array();
        $role = array();

        foreach (array_keys($user) as $key) {
            if (in_array($key, $this->roleColumns)) {
                $role[$key] = $user[$key];
            }
            if (in_array($key, $this->accountColumns)) {
                $account[$key] = $user[$key];
            }
        }

        return array($account, $role);
    }

    /**
     * Adds a user and its role
     *
     * @param array $user
     * @return user ID, status, message
     */
    public function add($user)
    {
        $return = array(
            'status'    => 0,
            'message'   => '',
            'id'        => 0,
        );
        list($account, $role) = $this->canonize($user);

        $row = Pi::model('user')->createRow($account);
        $row->prepare()->save();
        if (!$row->id) {
            $return['message'] = sprintf('User account "%s" is not created.', $user['identity']);
            return $return;
        }
        $role['user'] = $row->id;
        $roleRow = Pi::model('user_role')->createRow($role);
        $roleRow->save();
        if (!$roleRow->id) {
            $return['message'] = sprintf('User role "%s" is not created.', $user['identity']);
            return $return;
        }

        $return['status'] = 1;
        $return['id'] = $row->id;

        return $return;
    }

    /**
     * Updates a user and its role
     *
     * @param array $user
     * @return status, message
     */
    public function update($user)
    {
        $return = array(
            'status'    => 0,
            'message'   => '',
        );
        list($account, $role) = $this->canonize($user);

        if (!empty($account['id'])) {
            $row = Pi::model('user')->find($account['id']);
        } else {
            $row = Pi::model('user')->find($account['identity'], 'identity');
        }
        try {
            $row->assign($account);
            $row->save();
        } catch (\Exception $e) {
            $return['message'] = sprintf('User account "%s" is not saved.', $user['identity']);
            return $return;
        }

        $role['user'] = $row->id;
        $roleRow = Pi::model('user_role')->find($role['user'], 'user');
        if (!$roleRow) {
            $roleRow = Pi::model('user_role')->createRow($role);
        } else {
            $roleRow->assign($role);
        }
        try {
            $roleRow->save();
        } catch (\Exception $e) {
            $return['message'] = sprintf('User role "%s" is not saved.', $user['identity']);
            return $return;
        }

        $return['status'] = 1;
        $return['id'] = $row->id;

        return $return;
    }

    /**
     * Deletes a user and its role
     *
     * @param int|RowGateway $entity
     * @return array bool, message
     */
    public function delete($entity, $isRoot = false)
    {
        $return = array(
            'status'    => 0,
            'message'   => '',
        );
        $row = null;
        if ($entity instanceof RowGateway) {
            $row = $entity;
        } elseif (is_numeric($entity)) {
            $row = Pi::model('user')->find($entity);
        } else {
            $row = Pi::model('user')->find($entity, 'identity');
        }
        if (!$row) {
            $return['message'] = 'The user does not exist.';
            return $return;
        }
        $id = $row->id;

        // delete user account
        try {
            $row->delete();
        } catch (\Exception $e) {
            $return['message'] = 'Account is not deleted: ' . $e->getMessage();
            return $return;
        }

        // delete role
        try {
            Pi::model('user_role')->delete(array('user' => $id));
        } catch (\Exception $e) {
            $return['message'] = 'User role is not deleted: ' . $e->getMessage();
            return $return;
        }

        $return['status'] = 1;

        return $return;
    }
}
