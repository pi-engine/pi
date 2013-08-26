<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
 *
 *  - Role: follows DAG (Directed Acyclic Graph), i.e.
 *    - One role could inherit from multiple parent roles;
 *    - All permissions are checked through roles not users
 *  - Resource: one resource could inherit from one direct parent resource
 *    - Item: one resource could have multiple items
 *  - Privilege: one resource could have multiple privileges,
 *      or none as direct access
 *  - Rule: one rule specifies one role's access to one resource/item
 *      upon one specific privilege, default as `access`
 */
class Acl
{
    /**
     * Admin role
     * @var string
     */
    const ADMIN     = 'admin';

    /**
     * Regular member role
     * @var string
     */
    const MEMBER    = 'member';

    /**
     * Staff role
     * @var string
     */
    const STAFF     = 'staff';

    /**
     * Guest or visitor role
     * @var string
     */
    const GUEST     = 'guest';

    /**
     * Moderator staff role
     * @var string
     */
    const MODERATOR = 'moderator';

    /**
     * Banned account role
     * @var string
     */
    const DISABLED    = 'disabled';

    /**
     * Pending account role
     * @var string
     */
    const PENDING  = 'pending';

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
     * @var string[]
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
     * @var bool True for allowed and false for denied
     */
    protected $default;

    /**
     * Constructor
     *
     * @param string $section
     * @param bool $default
     */
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
        if ($this->getSection() == 'module'
            && method_exists($this->models[$modelName], 'setModule')
        ) {
            $this->models[$modelName]->setModule($this->getModule());
        }

        return $this->models[$modelName];
    }

    /**
     * Set section for resources
     *
     * Potential section names:
     *  - front: for front controllers and resources
     *  - admin: for admin controllers and resources
     *  - block: for blocks
     *
     * @param string $section  Section name
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
            $this->role = Pi::service('user')->getUser()
                ? Pi::service('user')->getUser()->role() : static::GUEST;
        }

        return $this->role;
    }

    /**
     * Add a rule to database
     *
     * @param bool|int      $allowed
     * @param string        $role
     * @param string        $section
     * @param string        $module
     * @param string|int    $resource
     * @param string|null   $privilege
     * @return bool
     */
    public static function addRule(
        $allowed,
        $role,
        $section,
        $module,
        $resource,
        $privilege = null
    ) {
        $deny = ($allowed > 0) ? 0 : 1;
        $rule = array(
            'section'   => $section,
            'role'      => $role,
            'resource'  => $resource,
            'deny'      => $deny,
            'module'    => $module,
        );
        if (null !== $privilege) {
            $rule['privilege'] = $privilege;
        }
        $rowRule = Pi::model('acl_rule')->createRow($rule);
        $rowRule->save();

        return true;
    }

    /**
     * Remove a rule to database
     *
     * @param string        $role
     * @param string        $section
     * @param string        $module
     * @param string|int    $resource
     * @param string|null   $privilege
     * @return bool
     */
    public static function removeRule(
        $role,
        $section,
        $module,
        $resource,
        $privilege = null
    ) {
        $rule = array(
            'section'   => $section,
            'role'      => $role,
            'resource'  => $resource,
            'module'    => $module,
        );
        if (null !== $privilege) {
            $rule['privilege'] = $privilege;
        }
        $rowRule = Pi::model('acl_rule')->select($rule)->current();
        if ($rowRule) {
            $rowRule->delete();
        }

        return true;
    }

    /**
     * Set a rule to database
     *
     * @param bool|int      $allowed
     * @param string        $role
     * @param string        $section
     * @param string        $module
     * @param string|int    $resource
     * @param string|null   $privilege
     * @return bool
     */
    public static function setRule(
        $allowed,
        $role,
        $section,
        $module,
        $resource,
        $privilege = null
    ) {
        $deny = ($allowed > 0) ? 0 : 1;
        $rule = array(
            'section'   => $section,
            'role'      => $role,
            'resource'  => $resource,
            'module'    => $module,
        );
        if (null !== $privilege) {
            $rule['privilege'] = $privilege;
        }
        $rowRule = Pi::model('acl_rule')->select($rule)->current();
        if ($rowRule) {
            $rowRule->deny = $deny;
            $rowRule->save();
        } else {
            $rowRule = Pi::model('acl_rule')->createRow($rule);
            $rowRule->deny = $deny;
            $rowRule->save();
        }

        return true;
    }

    /**
     * Check access to a resource privilege for a given role
     *
     * Support for two types of resources with `$resource`
     *
     *  - Named resources:
     *      - 'name' => <resource-name>
     *      - 'type' => <resource-type>
     *  - Controller actions:
     *      - 'module' => <module>
     *      - 'controller' => <controller>
     *      - 'action' => <action>
     *
     * @param string $role
     * @param string|array|object   $resource  Resource name
     * @param string                $privilege Privilege name
     * @return bool
     */
    public function isAllowed($role, $resource, $privilege = null)
    {
        if ($role == static::ADMIN) return true;

        $moduleRule = $this->getModel('rule');
        $where = array();

        if (null !== $privilege) {
            $where['privilege'] = $privilege;
        }

        $roles = $this->loadRoles($role);
        $where['role'] = $roles;

        $allowed = null;
        // Look up in all parent resources
        $resources = $this->loadResources($resource);

        while ($resources) {
            $where['resource'] = array_pop($resources);
            $allowed = $moduleRule->isAllowed($where);
            //d($allowed === null ? 'null' : intval($allowed));
            if (null !== $allowed) {
                break;
            }
        }
        // Return default permission is not defined
        $allowed = (null !== $allowed) ? $allowed : $this->getDefault();

        return $allowed;
    }

    /**
     * Check access to a resource privilege for a given role
     *
     * Support for two types of resources with `$resource`
     *
     *  - Named resources:
     *      - 'name' => <resource-name>
     *      - 'type' => <resource-type>
     *  - Controller actions:
     *      - 'module' => <module>
     *      - 'controller' => <controller>
     *      - 'action' => <action>
     *
     * @param string|array|object   $resource  Resource name
     * @param string                $privilege Privilege name
     * @return bool
     */
    public function checkAccess($resource, $privilege = null)
    {
        return $this->isAllowed($this->getRole(), $resource, $privilege);
    }

    /**
     * Check exceptions for admin page access to skip permission check
     *
     * @param array $resource Array of 'module', 'controller' and 'action'
     * @return bool
     */
    public function checkException($resource)
    {
        $module = $resource['module'];
        $controller = $resource['controller'];
        $action = $resource['action'];
        $pageList = Pi::registry('page')->read('exception', $module);

        // Page resource
        $key = sprintf('%s-%s-%s', $module, $controller, $action);
        if (isset($pageList[$key])) {
            return true;
        }
        $key = sprintf('%s-%s', $module, $controller);
        if (isset($pageList[$key])) {
            return true;
        }
        if (isset($pageList[$module])) {
            return true;
        }

        return false;
    }

    /**
     * Get resources to which a group of roles is allowed
     * to access a given resource privilege
     *
     * @param array|Where   $where
     * @param bool          $allowed
     * @return int[]
     */
    public function getResources($where = null, $allowed = true)
    {
        if ($this->getRole() == static::ADMIN) return null;
        $roles = $this->loadRoles();

        return $this->getModel('rule')->getResources($roles, $where, $allowed);
    }

    /**
     * Load ancestors of a role from database
     *
     * @param string $role
     * @return string[]
     */
    public function loadRoles($role = null)
    {
        if (null !== $role && $role != $this->getRole()) {
            $roles = Pi::registry('role')->read($role);
            array_push($roles, $role);
            return $roles;
        }
        if (null === $this->roles) {
            $this->roles = Pi::registry('role')->read(
                $this->getRole()
            ) ?: array(); array_push($this->roles, $this->getRole());
        }

        return $this->roles;
    }

    /**
     * Load ancestors of a resource from database
     *
     * Support for three types of resources with `$resource`
     *
     *  - Named resources:
     *      - 'name' => <resource-name>
     *      - 'type' => <resource-type>
     *  - Controller actions:
     *      - 'module' => <module>
     *      - 'controller' => <controller>
     *      - 'action' => <action>
     *  - {@link Node}
     *
     * @param string|array|Node  $resource  Resource name
     * @return string[]
     */
    public function loadResources($resource)
    {
        $resources = array();
        // Routed resource with module-controller-action
        if (is_array($resource) && isset($resource['module'])) {
            $module = $resource['module'];
            $controller = $resource['controller'];
            $action = $resource['action'];
            $section = empty($resource['section'])
                ? $this->getSection() : $resource['section'];
            $resourceList = Pi::registry('resource')->read(
                $section,
                $module,
                'page'
            );
            $pageList = array_flip(Pi::registry('page')->read(
                $section,
                $module
            ));

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

            return $resources;
        }

        // Application resource
        if (is_numeric($resource)) {
            $resources = array($resource);
            return $resources;
        }
        $type = 'system';
        if (is_array($resource)) {
            $type = isset($resource['type']) ? $resource['type'] : 'system';
            $name = $resource['name'];
        } else {
            $name = $resource;
        }

        $resourceList = Pi::registry('resource')->read(
            $this->getSection(),
            $this->getModule(),
            $type
        );
        if (isset($resourceList[$name])) {
            $resources = $resourceList[$name];
        } else {
            $resources = array($name);
        }

        return $resources;
    }
}
