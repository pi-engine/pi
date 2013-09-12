<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;
use Pi\Application\AbstractApi;

/**
 * Permission handling service
 *
 * APIs:
 *
 * - grantPermission($role, array $permissions)
 * - revokePermission($role, array $permissions = array())
 * - getPermission($role, array $condition = array())
 * - hasPermission($permission, $uid = null)
 * - inheritPermission($role, $fromRole)
 * - getRoles($uid = null)
 * - isAdminRole($role)
 * - isAdmin($uid = null)
 *
 * Sample code:
 *
 * ```
 *  // Grant permissions to a role
 *  Pi::permission()->grantPermission(<role-name>, array(<perm-name>));
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Permission extends AbstractService
{
    /**
     * Application section: front, admin
     * @var string
     */
    protected $section;

    protected $roles = array(
        'front' => array(
            'admin' => 'admin',
        ),
        'admin' => array(
            'admin' => 'root',
        ),
    );

    /**
     * Set section
     *
     * @param string $section
     *
     * @return bool
     */
    public function setSection($section = '')
    {
        $this->section = (string) $section;

        return true;
    }

    /**
     * Get current section
     *
     * @return string
     */
    public function getSection()
    {
        if (!$this->section) {
            $this->section = Pi::engine()->application()->getSection();
        }

        return $this->section;
    }

    /**
     * Get permission data model
     *
     * @return object
     */
    public function model()
    {
        return Pi::model('permission');
    }

    /**
     * Grant permission to a role
     *
     * @param string $role
     * @param array $permission Perm specs: section, module, resource, item
     *
     * @return bool
     */
    public function grantPermission($role, array $permission)
    {
        $result = true;
        if (!isset($permission['section'])) {
            $permission['section'] = $this->getSection();
        }
        $permission['role'] = $role;
        try {
            $rowset = $this->model()->select($permission);
        } catch (\Exception $e) {
            return false;
        }
        if ($rowset->count()) {
            return true;
        }
        $row = $this->model()->createRow($permission);
        try {
            $row->save();
        } catch (\Exception $e) {
            return false;
        }

        return $result;
    }

    /**
     * Revoke permission from a role
     *
     * All permissions will be revoked if no permission is specified
     *
     * @param string $role
     * @param array $permission
     *
     * @return bool
     */
    public function revokePermission($role, array $permission = array())
    {
        $result = true;
        if (!isset($permission['section'])) {
            $permission['section'] = $this->getSection();
        }
        $permission['role'] = $role;
        try {
            $this->model()->delete($permission);
        } catch (\Exception $e) {
            return false;
        }

        return $result;
    }

    /**
     * Check if a user or role(s) has permission
     *
     * @param array $permission
     * @param null|int|string|string[]  $uid
     *
     * @return bool
     */
    public function hasPermission(array $permission, $uid = null)
    {
        if (null === $uid) {
            $uid = Pi::user()->getIndentity();
        }
        if (is_numeric($uid)) {
            $roles = $this->getRoles($uid);
        } else {
            $roles = (array) $uid;
        }
        $permission['role'] = $roles;
        if (!isset($permission['section'])) {
            $permission['section'] = $this->getSection();
        }
        $select = $this->model->select();
        $select->where($permission)->limit(1);
        $rowset = $this->model()->selectWith($select);
        $result = $rowset->count() ? true : false;

        return $result;
    }

    /**
     * Get permissions of a role subject to conditions
     *
     * @param string $role
     * @param array $condition
     *
     * @return array
     */
    public function getPermission($role, array $condition = array())
    {
        $result = array();
        if (!isset($condition['section'])) {
            $condition['section'] = $this->getSection();
        }
        $condition['role'] = $role;
        $rowset = $this->model()->select($condition);
        foreach ($rowset as $row) {
            $result[] = array(
                'module'    => $row['module'],
                'resource'  => $row['resource'],
                'item'      => $row['item'],
            );
        }

        return $result;
    }

    /**
     * Get roles of a user
     *
     * @param int|null $uid
     * @param string   $section
     * @param string   $section
     *
     * @return string[]
     */
    public function getRoles($uid = null, $section = '')
    {
        $result = array();

        $uid = null !== $uid ? (int) $uid : Pi::user()->getIndentity();
        $section = $section ?: $this->getSection();
        $rowset = Pi::Model('user_role')->select(array(
            'uid'       => $uid,
            'section'   => $section,
        ));
        foreach ($rowset as $row) {
            $result[] = $row['role'];
        }

        return $result;
    }

    /**
     * Check if a role is admin role
     *
     * @param string $role
     * @param string $module
     *
     * @return bool
     */
    public function isAdminRole($role, $module = '')
    {
        $section = $this->getSection();
        $result = ($role == $this->roles[$section]['admin']) ? true : false;
        if (!$result && $module) {
            $result = $this->hasPermission(array(
                'resource'  => 'module-admin',
                'item'      => $module,
            ), $role);
        }

        return $result;
    }

    /**
     * Check if a user is admin
     *
     * @param string $module
     * @param int|null $uid
     *
     * @return bool
     */
    public function isAdmin($module = '', $uid = null)
    {
        $result = false;
        $section = $this->getSection();
        $uid = null !== $uid ? (int) $uid : Pi::user()->getIndentity();
        $roles = $this->getRoles($uid);
        if (in_array($this->roles[$section]['admin'], $roles)) {
            $result = true;
        }
        if (!$result && $module) {
            $result = $this->hasPermission(array(
                'resource'  => 'module-admin',
                'item'      => $module,
            ), $uid);
        }

        return $result;
    }
}
