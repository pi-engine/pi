<?php
/**
 * Permission ACL class
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
 * @package         Pi\ACL
 * @version         $Id$
 */

namespace Pi\Acl;
use Pi;
use Pi\Db\RowGateway\RowGateway;
use Pi\Db\RowGateway\Node;
use Zend\Db\Sql\Where;

/**
 * Permission ACL manager
 *
 * Handles:
 *  1. Role: follows DAG (Directed Acyclic Graph), i.e. one role could inherit from multiple parent roles; All permissions are checked through roles not users
 *  2. Resource: one resource could inherit from one direct parent resource
 *      2.1 Item: one resource could have multiple items
 *  3. Privilege: one resource could have multiple privileges, or none as direct access
 *  4. Rule: one rule specifies one role's access to one resource/item upon one specific priviledge, default as access
 */
class Acl
{
    /**
     * Admin role
     */
    const ADMIN     = 'admin';
    /**
     * Regular member role
     */
    const MEMBER    = 'member';
    /**
     * Guest or visitor role
     */
    const GUEST     = 'guest';
    /**
     * Moderator role
     */
    const MODERATOR = 'moderator';
    /**
     * Banned account role
     */
    const BANNED    = 'banned';
    /**
     * Pending account role
     */
    const INACTIVE  = 'inactive';

    /**
     * Application section
     * @var string
     */
    protected $section;

    /**
     * Applied module
     * @var string
     */
    protected $module;

    /**
     * Current role
     * @var string
     */
    protected $role;

    /**
     * Ancestor roles or current role
     * @var array
     */
    protected $roles;

    /**
     * Models for rule, resource, privilege and rule
     * @var array
     */
    protected $models = array();

    /**
     * Default permission when a rule is not specified
     *
     * @var bool
     */
    protected $default;

    public function __construct($section = null, $default = null)
    {
        if (null !== $section) {
            $this->section = $section;
        }
        if (null !== $default) {
            $this->default = $default;
        }
    }

    /**
     * Gets a model and set section/module if applicable
     *
     * @param string $modelName
     * @return RowGateway
     */
    public function getModel($modelName)
    {
        if (!isset($this->models[$modelName])) {
            $model = Pi::model('acl_' . $modelName);
            $this->models[$modelName] = $model;
        }
        if (method_exists($this->models[$modelName], 'setSection')) {
            $this->models[$modelName]->setSection($this->getSection());
        }
        if ($this->getSection() == 'module' && method_exists($this->models[$modelName], 'setModule')) {
            $this->models[$modelName]->setModule($this->getModule());
        }

        return $this->models[$modelName];
    }

    /**
     * Set section for resources
     *
     * @param string $section  section name, potential values: front - 'front'; admin - 'admin'; block - 'block'
     * @return Acl
     */
    public function setSection($section)
    {
        if (null !== $section) {
            $this->section = $section;
        }
        return $this;
    }

    /**
     * Get current application section
     *
     * @return string
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * Set default permission
     *
     * @param bool $default Default permission
     * @return Acl
     */
    public function setDefault($default)
    {
        if (null !== $default) {
            $this->default = (bool) $default;
        }
        return $this;
    }

    /**
     * Get default permission
     *
     * @return bool
     */
    public function getDefault()
    {
        if (null === $this->default) {
            return 'admin' == $this->section ? false : true;
        }
        return $this->default;
    }

    /**
     * Set current module
     *
     * @param string $module
     * @return Acl
     */
    public function setModule($module)
    {
        if (!is_null($module)) {
            $this->module = $module;
        }
        return $this;
    }

    /**
     * Get current module
     *
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set current role
     *
     * @param string $role
     * @return Acl
     */
    public function setRole($role)
    {
        if (null !== $role) {
            if ($role != $this->role) {
                $this->roles = null;
            }
            $this->role = $role;
        }
        return $this;
    }

    /**
     * Get current role, load from current authenticated user if not set
     *
     * @return string
     */
    public function getRole()
    {
        if (null === $this->role) {
            $this->role = Pi::registry('user') ? Pi::registry('user')->role : null;
        }
        return $this->role;
    }

    /**
     * Check access to a resource privilege for a given role
     *
     * @param string $role
     * @param string|array|object  $resource  resource name or array($resource, $item), or array('module' => $module, 'controller' => $controller, 'action' => $action)
     * @param string $privilege privilege name
     * @return boolean
     */
    public function isAllowed($role, $resource, $privilege = null)
    {
        if ($role == static::ADMIN) return true;

        $where = array();

        $resources = $this->loadResources($resource);
        if (empty($resources)) {
            return $this->getDefault();
        } elseif (count($resources) == 1) {
            $where['resource'] = $resources[0];
        } else {
            $where['resource'] = $resources;
        }
        if (null !== $privilege) {
            $where['privilege'] = $privilege;
        }


        $roles = $this->loadRoles($role);
        if (count($roles) == 1) {
            $where['role'] = $roles[0];
        } else {
            $where['role'] = $roles;
        }

        $moduleRule = $this->getModel('rule');
        $allowed = (bool) $moduleRule->isAllowed($where, $this->getDefault());

        return $allowed;
    }

    /**
     * Check access to a resource privilege for a given role
     *
     * @param string|array|object  $resource  resource name or array($resource, $item), or array('module' => $module, 'controller' => $controller, 'action' => $action)
     * @param string    $privilege privilege name
     * @return boolean
     */
    public function checkAccess($resource, $privilege = null)
    {
        return $this->isAllowed($this->getRole(), $resource, $privilege);
    }

    /**
     * Get resources to which a group of roles is allowed/denied to access a given resource privilege
     *
     * @param array|Where    $where
     * @param boolean   $allowed allowed or denied
     * @return array of resources
     */
    public function getResources($where = null, $allowed = true)
    {
        if ($this->getRole() == static::ADMIN) return null;
        $roles = $this->loadRoles();
        return $this->getModel('rule')->getResources($roles, $where, $allowed);
    }

    /**
     * Get items of a specific resource to which a group of roles is allowed/denied to access a given privilege
     *
     * @param string    $resource
     * @param array|Where $clause
     * @param boolean   $allowed allowed or denied
     * @return array of items
     */
    public function getItems($resource, $where = null, $allowed = true)
    {
        if ($this->getRole() == static::ADMIN) return null;
        $roles = $this->loadRoles();
        if (!$where instanceof Where) {
            $where['resource'] = $resource;
            $where = new Where($where);
        } else {
            $where->equalTo('resource', $resource);
        }
        return $this->getModel('rule')->getResources($roles, $where, $allowed);
    }

    /**
     * Load ancestors of a role from database
     *
     * @param string $role
     * @return array of roles
     */
    public function loadRoles($role = null)
    {
        if (null !== $role && $role != $this->getRole()) {
            $roles = Pi::service('registry')->role->read($role);
            array_push($roles, $role);
            return $roles;
        }
        if (null === $this->roles) {
            $this->roles = Pi::service('registry')->role->read($this->getRole()) ?: array();
            array_push($this->roles, $this->getRole());
        }
        return $this->roles;
    }

    /**
     * Load ancestors of a resource from database
     *
     * @param string|array|Node  $resource  resource name or array($resource, $item),
     *                                          or array('module' => $module, 'controller' => $controller, 'action' => $action)
     *                                          or {@link Node}
     * @return array of resources
     */
    public function loadResources($resource)
    {
        // Dispatch resource
        if (is_array($resource) && isset($resource['module'])) {
            $module = $resource['module'];
            $controller = $resource['controller'];
            $action = $resource['action'];
            $resourceList = Pi::service('registry')->resource->read($this->getSection(), $module);
            $pageList = array_flip(Pi::service('registry')->page->read($this->getSection(), $module));
            $resources = array();
            foreach ($resourceList as $page => $list) {
                // Generated from page or named
                $key = isset($pageList[$page]) ? $pageList[$page] : $page;
                $resources[$key] = $list;
            }
            // Page resource
            $key = sprintf('%s-%s-%s', $module, $controller, $action);
            if (isset($resources[$key])) {
                return $resources[$key];
            }
            $key = sprintf('%s-%s', $module, $controller);
            if (isset($resources[$key])) {
                return $resources[$key];
            }
            if (isset($resources[$module])) {
                return $resources[$module];
            }
            return array();
        }

        // Appliction resource
        $resources = array();
        $modelResource = $this->getModel('resource');
        if (!$resource instanceof Node) {
            $where = array('section' => $this->getSection());
            if ($this->getModule()) {
                $where['module'] = $this->getModule();
            }
            if (is_array($resource)) {
                $where['name'] = $resource[0];
                $where['item'] = $resource[1];
            } else {
                $where['name'] = $resource;
            }
            $result = $modelResource->select($where);
            if (!$result->count()) {
                return $resources;
            }
            $resource = $result->current();
        }
        $resources = $modelResource->getAncestors($resource, 'id');
        return $resources;
    }
}
