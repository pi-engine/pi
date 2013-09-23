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
 * Default roles:
 *
 * - Front: guest, member, admin
 * - Admin: webmaster
 *
 * APIs:
 *
 * - grantPermission($role, array $permissions)
 * - revokePermission($role, array $permission = array())
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
    /** @var int Root user id */
    const ROOT_UID = 1;

    /**
     * Application section: front, admin
     * @var string
     */
    protected $section;

    /**
     * Predefined roles
     * @var array
     */
    protected $roles = array(
        'front' => array(
            'admin' => 'webmaster',
            'guest' => 'guest',
        ),
        'admin' => array(
            'admin' => 'admin',
        ),
    );

    /** @var array Rule columns */
    protected $columns = array(
        'section', 'module', 'resource'
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
        return Pi::model('permission_rule');
    }

    /**
     * Grant permission to a role
     *
     * Resource spec:
     * - section: front, admin
     * - module
     * - resource: <resource-name>, block-<block-id>, module-<access|admin|manage|setting>
     *
     * @param string $role
     * @param array $resource Specs: section, module, resource
     *
     * @return bool
     */
    public function grantPermission($role, array $resource)
    {
        $result = true;
        $rule = $this->canonizeRule($resource);
        $rule['role'] = $role;
        try {
            $rowset = $this->model()->select($rule);
        } catch (\Exception $e) {
            return false;
        }
        if ($rowset->count()) {
            return true;
        }
        $row = $this->model()->createRow($rule);
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
     * @param array $resource
     *
     * @return bool
     */
    public function revokePermission($role, array $resource = array())
    {
        $result = true;
        $rule = $this->canonizeRule($resource);
        $rule['role'] = $role;
        try {
            $this->model()->delete($rule);
        } catch (\Exception $e) {
            return false;
        }

        return $result;
    }

    /**
     * Check if a user or role(s) has permission
     *
     * @param array $resource   Array: section, module, resource
     * @param null|int|string|string[]  $uid Int for uid and string for role
     *
     * @return bool
     */
    public function hasPermission(array $resource, $uid = null)
    {
        $roles = $this->canonizeRole($uid);
        if (!$roles) {
            return false;
        }
        $rule = $this->canonizeRule($resource);
        $rule['role'] = $roles;
        //vd($rule);
        $select = $this->model()->select();
        $select->where($rule)->limit(1);
        $rowset = $this->model()->selectWith($select);
        $result = $rowset->count() ? true : false;

        return $result;
    }

    /**
     * Get permitted resources of a role subject to conditions
     *
     * @param null|int|string|string[] $role Int for uid and string for role
     * @param array $condition
     *
     * @return array
     */
    public function getPermission($role, array $condition = array())
    {
        $result = array();
        $condition = $this->canonizeRule($condition);
        $condition['role'] = $this->canonizeRole($role);
        $rowset = $this->model()->select($condition);
        foreach ($rowset as $row) {
            $result[] = array(
                'module'    => $row['module'],
                'resource'  => $row['resource'],
                //'item'      => $row['item'],
            );
        }

        return $result;
    }

    /**
     * Check permission for a module
     *
     * @param string $module
     * @param null|int|string|string[]  $uid Int for uid and string for role
     * @param string $permission
     *      Permission type: front - access, admin; admin - manage, admin
     * @param string $section
     *
     * @return bool
     */
    public function modulePermission(
        $module,
        $uid        = null,
        $permission = '',
        $section    = ''
    ) {
        $permission = $permission ?: 'access';
        $section = $section ?: $this->getSection();
        $resource = array(
            'section'   => $section,
            'module'    => $module,
            'resource'  => 'module-' . $permission
        );
        $result = $this->hasPermission($resource, $uid);

        return $result;
    }

    /**
     * Get front permitted module list
     *
     * @param string $permission
     *      Permission type: front - access, admin; admin - manage, admin
     * @param string $section
     * @param null|int|string|string[]  $uid Int for uid and string for role
     *
     * @return string[]
     */
    public function moduleList($permission, $section = '', $uid = null)
    {
        $result = array();
        $permission = $permission ?: 'access';
        $section = $section ?: $this->getSection();
        $condition = array(
            'section'   => $section,
            'resource'  => 'module-' . $permission
        );
        $rules = $this->getPermission($uid, $condition);
        foreach ($rules as $rule) {
            $result[] = $rule['module'];
        }

        return $result;
    }

    /**
     * Check permission for a block
     *
     * @param int $id Block id
     * @param null|int|string|string[]  $uid Int for uid and string for role
     *
     * @return bool
     */
    public function blockPermission($id, $uid = null) {
        $resource = array(
            'section'   => 'front',
            'resource'  => 'block-' . $id
        );
        $result = $this->hasPermission($resource, $uid);

        return $result;
    }

    /**
     * Get permitted block list from a given block list
     *
     * @param int[] $blocks
     * @param null|int|string|string[]  $uid Int for uid and string for role
     *
     * @return int[]
     */
    public function blockList(array $blocks, $uid = null)
    {
        array_walk($blocks, function (&$block, $key) {
            $block = 'block-' . $block;
        });
        $condition = array(
            'section'   => 'front',
            'resource'  => $blocks
        );
        $rules = $this->getPermission($uid, $condition);
        array_walk($rules, function (&$rule, $key) {
            $rule = (int) substr($rule['resource'], 6);
        });

        return $rules;
    }

    /**
     * Check if a page is accessible
     *
     * @param array $route
     * @param null|int|string|string[]  $uid Int for uid and string for role
     *
     * @return bool|null
     */
    public function pagePermission(array $route, $uid = null)
    {
        $access = null;

        $section = $module = $controller = $action = null;
        extract($route);
        $section = $section ?: $this->getSection();
        $type = 'page';
        $pages = Pi::registry('permission_resource')->read(
            $section,
            $module,
            $type
        );
        //vd($pages);
        // Page resource
        $resource = '';
        $key = sprintf('%s-%s-%s', $module, $controller, $action);
        if (isset($pages[$key])) {
            $resource = $pages[$key];
        } else {
            $key = sprintf('%s-%s', $module, $controller);
            if (isset($pages[$key])) {
                $resource = $pages[$key];
            }
        }
        if ($resource) {
            $access = $this->hasPermission(array(
                'section'   => $section,
                'module'    => $module,
                'resource'  => $resource,
            ), $uid);
        }

        return $access;
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

        $uid = (int) (null !== $uid ? $uid : Pi::user()->getIndentity());
        $section = $section ?: $this->getSection();
        if (!$uid) {
            $result[] = $this->roles[$section]['guest'];
        } else {
            $rowset = Pi::Model('user_role')->select(array(
                'uid'       => $uid,
                'section'   => $section,
            ));
            foreach ($rowset as $row) {
                $result[] = $row['role'];
            }
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
     * @param string $section
     *
     * @return bool
     */
    public function isAdmin($module = '', $uid = null, $section = '')
    {
        $result = false;
        $section = $section ?: $this->getSection();
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

    /**
     * Check if user is root user
     *
     * @param null|int $uid
     *
     * @return bool
     */
    public function isRoot($uid = null)
    {
        $uid = null !== $uid ? (int) $uid : Pi::user()->getIndentity();
        $result = static::ROOT_UID === $uid ? true : false;

        return $result;
    }

    /**
     * Canonize role(s)
     *
     * @param null|int|string|string[] $role Int for uid and string for role
     *
     * @return string[]
     */
    public function canonizeRole($role)
    {
        // uid
        if (null === $role) {
            $role = (int) Pi::user()->getIdentity();
        }
        // uid => roles
        if (is_numeric($role)) {
            $roles = $this->getRoles($role);
        // role
        } else {
            $roles = (array) $role;
        }

        return $roles;
    }

    /**
     * Canonize rule data
     *
     * @param array $rule Array: section, module, resource
     *
     * @return array
     */
    protected function canonizeRule(array $rule)
    {
        foreach ($rule as $key => $val) {
            if (!in_array($key, $this->columns)) {
                unset($rule[$key]);
            }
        }
        if (!isset($rule['section'])) {
            $rule['section'] = $this->getSection();
        }

        return $rule;
    }
}
