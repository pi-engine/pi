<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application\Installer\Resource;

use Pi;
use Pi\Acl\Acl as AclHandler;

/**
 * Setup ACL component with configuration specs
 *
 * ```
 *  array(
 *      'roles' => array(
 *          'admin' => array(
 *              <role-name>  => array(
 *                  'title'     => 'Title',
 *                  'parents'   => array('parent'),
 *              ),
 *              <role-name> => array(
 *                  'title'     => 'Title',
 *                  'parents'   => array('parent'),
 *              ),
 *              <...>
 *          ),
 *          'front' => array(
 *              <role-name>  => array(
 *                  'title'     => 'Title',
 *                  'parents'   => array('parent'),
 *              ),
 *              <role-name> => array(
 *                  'title'     => 'Title',
 *                  'parents'   => array('parent'),
 *              ),
 *              <...>
 *          ),
 *      ),
 *      'resources' => array(
 *          // Front resources
 *          'front'    => array(
 *              'category'  => array(
 *                  //'name'          => 'category',
 *                  'title'         => 'Category Title',
 *                  'parent'        => 'parentCategory'
 *                  'access'        => array(
 *                      'guest'     => 1,
 *                      'member'    => 1
 *                  ),
 *                  'privileges'    => array(
 *                      'read'      => array(
 *                          'title' => 'Read articles',
 *                      ),
 *                      'post'      => array(
 *                          'title' => 'Post articles',
 *                          'access' => array(
 *                              'guest'     => 0,
 *                          ),
 *                      ),
 *                      'delete'    => array(
 *                          'title' => 'Post articles',
 *                          'access' => array(
 *                              'guest'     => 0,
 *                              'member'    => 0,
 *                          ),
 *                      ),
 *                  ),
 *              ),
 *              ...
 *          ),
 *          'admin' => array(
 *              ...
 *          ),
 *          'custom' => array(
 *              ...
 *          ),
 *      ),
 *  );
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */

class Acl extends AbstractResource
{
    /**
     * Canonize ACL resource config
     *
     * @param array $resource
     * @return array
     */
    protected function canonizeResource($resource)
    {
        $columns = array(
            'section', 'name', 'item', 'title', 'module', 'type'
        );
        $data = array();
        foreach ($columns as $col) {
            if (isset($resource[$col])) {
                $data[$col] = $resource[$col];
            }
        }

        return $data;
    }

    /**
     * Canonize role data
     *
     * @param array $roles
     *
     * @return array
     */
    protected function canonizeRole(array $roles)
    {
        $module = $this->getModule();
        $result = array();
        foreach ($roles as $section => $list) {
            foreach ($list as $name => $role) {
                $result[$name] = array_merge(array(
                    'name'      => $name,
                    'module'    => $module,
                    'section'   => $section,
                ), $role);
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function installAction()
    {
        $module = $this->event->getParam('module');
        // Create module access permissions
        // System module permissions
        if ('system' == $module) {
            $modulePerms = array(
                'front' => array(
                    'guest'     => 1,
                    'member'    => 1,
                ),
                'admin' => array(
                    'manager'   => 1,
                    'staff'     => 0,
                ),
                /*
                'operation' => array(
                    'manager'   => 1,
                    'staff'     => 0,
                ),
                */
                'manage' => array(
                    'manager'   => 1,
                    'staff'     => 0,
                )
            );
        // Regular module permissions
        } else {
            $modulePerms = array(
                'front' => array(
                    'guest'     => 1,
                    'member'    => 1,
                ),
                'admin' => array(
                    'staff'     => 0,
                    'editor'    => 1,
                ),
                /*
                'operation' => array(
                    'editor'    => 1,
                    'staff'     => 0,
                ),
                */
                'manage' => array(
                    'moderator' => 1,
                    'staff'     => 0,
                )
            );
        }
        // Add permission rules
        $modelRule = Pi::model('acl_rule');
        foreach ($modulePerms as $section => $access) {
            foreach ($access as $role => $rule) {
                AclHandler::addRule(
                    $rule,
                    $role,
                    'module-' . $section,
                    $module,
                    $module
                );
            }
        }

        Pi::registry('moduleperm')->flush();

        if (empty($this->config)) {
            return true;
        }

        // Add roles
        if (!empty($this->config['roles'])) {
            $inheritance = array();
            $roles = $this->canonizeRole($this->config['roles']);
            foreach ($roles as $key => $role) {
                //$role['name'] = $name;
                //$role['module'] = $module;
                if (isset($role['parents'])) {
                    $inheritance[$role['name']] = $role['parents'];
                    unset($role['parents']);
                }
                $message = array();
                $status = $this->insertRole($role, $message);
                if (false === $status) {
                    $message[] = sprintf(
                        'Role "%s" is not created.',
                        $key
                    );
                    return array(
                        'status'    => false,
                        'message'   => $message
                    );
                }
            }
            if (!empty($inheritance)) {
                foreach ($inheritance as $child => $parents) {
                    foreach ($parents as $parent) {
                        $inherit = compact('child', 'parent');
                        $message = array();
                        $status = $this->insertInherit($inherit, $message);
                        if (false === $status) {
                            $message[] = sprintf(
                                'Inherit "%s-%s" is not created.',
                                $child,
                                $parent
                            );
                            return array(
                                'status'    => false,
                                'message'   => $message
                            );
                        }
                    }
                }
            }
        }

        // Add resources
        $resources = isset($this->config['resources'])
            ? $this->config['resources'] : array();
        foreach ($resources as $section => $resourceList) {
            foreach ($resourceList as $name => $resource) {
                $resource['name'] = $name;
                $resource['module'] = isset($resource['module'])
                    ? $resource['module'] : $module;
                $resource['section'] = $section;
                $resource['type'] = 'system';
                $message = array();
                $status = $this->insertResource($resource, $message);
                if (!$status) {
                    $message[] = sprintf(
                        'Resource "%s" is not created.',
                        $resource['name']
                    );
                    return array(
                        'status'    => false,
                        'message'   => $message,
                    );
                }
            }
        }

        Pi::registry('role')->flush();
        Pi::registry('resource')->flush();

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function updateAction()
    {
        $module = $this->event->getParam('module');
        if ($this->skipUpgrade()) {
            return;
        }

        // Update roles
        $roles = $this->canonizeRole($this->config['roles']);
        $rolesNew = $roles;

        $model = Pi::model('acl_role');
        $rowset = $model->select(array(
            'module'    => $module,
            //'type'      => 'system',
        ));
        $rolesExist = array();
        foreach ($rowset as $row) {
            $rolesExist[$row->name] = $row;
        }
        $inheritanceNew = array();
        //$inheritanceDelete = array();
        foreach ($rolesNew as $key => $role) {
            //$role['name'] = $name;
            //$role['module'] = $module;
            if (isset($role['parents'])) {
                foreach ($role['parents'] as $parent) {
                    $inheritanceNew[$role['name']][$parent] = 1;
                }
                unset($role['parents']);
            }
            // Update existent role
            if (isset($rolesExist[$key])) {
                if ($rolesExist[$key]->title != $role['title']) {
                    $rolesExist[$key]->title = $role['title'];
                    $rolesExist[$key]->save();
                }
                unset($rolesExist[$key]);
                continue;
            }
            // Add new role
            $message = array();
            $status = $this->insertRole($role, $message);
            if (false === $status) {
                $message[] = sprintf(
                    'Role "%s" is not created.',
                    $key
                );
                return array(
                    'status'    => false,
                    'message'   => $message
                );
            }
        }
        // Delete deprecated roles
        $rolesDelete = array();
        foreach ($rolesExist as $row) {
            $rolesDelete[$row->name] = 1;
            $row->delete();
        }

        $rowset = Pi::model('acl_inherit')->select(array());
        foreach ($rowset as $row) {
            // Delete inheritance linked to deleted roles
            if (isset($rolesDelete[$row->child])
                || isset($rolesDelete[$row->parent])) {
                $row->delete();
            // Mark existent links
            } else {
                $inheritanceNew[$row->child][$row->parent] = 0;
            }
        }
        foreach ($inheritanceNew as $child => $parents) {
            foreach ($parents as $parent => $flag) {
                if (!$flag) {
                    continue;
                }
                // Add new inheritance
                $row = Pi::model('acl_inherit')->createRow(array(
                    'child'     => $child,
                    'parent'    => $parent,
                ));
                $row->save();
            }
        }

        // Update resources
        $resources_new = isset($this->config['resources'])
            ? $this->config['resources'] : array();

        $model = Pi::model('acl_resource');
        $rowset = $model->select(array(
            'module'    => $module,
            'type'      => 'system',
        ));
        // Find existent resources
        $resources_exist = array();
        foreach ($rowset as $row) {
            $resources_exist[$row->section][$row->name] = $row->toArray();
        }

        foreach ($resources_new as $section => $resourceList) {
            foreach ($resourceList as $name => $resource) {
                $resource['name'] = $name;
                $resource['module'] = empty($resource['module'])
                    ? $module : $resource['module'];
                $resource['section'] = $section;
                $resource['type'] = 'system';
                // Update existent resource
                if (isset($resources_exist[$section][$name])) {
                    $resource['id'] = $resources_exist[$section][$name]['id'];
                    $message = array();
                    $status = $this->updateResource($resource, $message);
                    unset($resources_exist[$section][$name]);
                    if (!$status) {
                        $message[] = sprintf(
                            'Resource "%s" is not updated.',
                            $resource['name']
                        );
                        return array(
                            'status'    => false,
                            'message'   => $message,
                        );
                    }
                    continue;
                }
                $message = array();
                // Add new resource
                $status = $this->insertResource($resource, $message);
                if (!$status) {
                    $message[] = sprintf(
                        'Resource "%s" is not created.',
                        $resource['name']
                    );
                    return array(
                        'status'    => false,
                        'message'   => $message,
                    );
                }
            }
        }

        // Remove not deprecated resources
        foreach ($resources_exist as $section => $resourceList) {
            foreach ($resourceList as $name => $resource) {
                $message = array();
                $status = $this->deleteResource($resource, $message);
                if (!$status) {
                    $message[] = sprintf(
                        'Resource "%s" is not deleted.',
                        $resource['name']
                    );
                    return array(
                        'status'    => false,
                        'message'   => $message,
                    );
                }
            }
        }

        Pi::registry('role')->flush();
        Pi::registry('resource')->flush();

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function uninstallAction()
    {
        $module = $this->event->getParam('module');

        $rowset = Pi::model('acl_role')->select(array('module' => $module));
        $roles = array();
        foreach ($rowset as $row) {
            $roles[] = $row->id;
            $row->delete();
        }
        if ($roles) {
            $where = Pi::db()->where();
            $where->in('child', $roles)->in('parent', $roles, 'OR');
            Pi::model('acl_inherit')->delete($where);
        }

        $rowset = Pi::model('acl_resource')->select(array(
            'module'    => $module,
            'type'      => 'system'
        ));
        //$resources = array();
        foreach ($rowset as $row) {
            //$resources[] = $row->id;
            $this->deleteResource($row);
        }
        /*
        if ($resources) {
            $where = array('module' => $module, 'resource' => $resources);
            foreach (array('acl_rule', 'acl_privilege') as $modelName) {
                Pi::model($modelName)->delete($where);
            }
        }
        */
        Pi::model('acl_rule')->delete(array('module' => $module));

        Pi::registry('moduleperm')->flush();
        Pi::registry('role')->flush();
        Pi::registry('resource')->flush();

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function activateAction()
    {
        $module = $this->event->getParam('module');
        $where = array('module' => $module);
        $model = Pi::model('acl_role');
        $model->update(array('active' => 1), $where);

        Pi::registry('role')->flush();
        Pi::registry('resource')->flush();

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function deactivateAction()
    {
        $module = $this->event->getParam('module');
        $where = array('module' => $module);
        $model = Pi::model('acl_role');
        $model->update(array('active' => 0), $where);

        Pi::registry('role')->flush();
        Pi::registry('resource')->flush();

        return true;
    }

    /**
     * Create a role
     *
     * @param array $role
     * @param array $message
     * @return bool
     */
    protected function insertRole($role, &$message)
    {
        $model = Pi::model('acl_role');
        $row = $model->createRow($role);
        $row->save();

        return $row->id ? true : false;
    }

    /**
     * Create role inheritance
     *
     * @param array $pair
     * @param array $message
     * @return bool
     */
    protected function insertInherit($pair, &$message)
    {
        $model = Pi::model('acl_inherit');
        $row = $model->createRow($pair);
        $row->save();

        return $row->id ? true : false;
    }

    /**
     * Inerset ACL resource
     *
     * @param array $resource
     * @param array $message
     * @return bool
     */
    protected function insertResource($resource, &$message)
    {
        $modelResource = Pi::model('acl_resource');
        //$modelRule = Pi::model('acl_rule');
        $modelPrivilege = Pi::model('acl_privilege');

        $data = $this->canonizeResource($resource);
        // Get parent resource
        if (!empty($resource['parent'])) {
            if (is_string($resource['parent'])) {
                $where = array(
                    'section'   => $resource['section'],
                    'modul'     => $resource['module'],
                    'name'      => $resource['parent']
                );
            // use parent params if available
            } elseif (is_array($resource['parent'])) {
                $where = array_merge(array(
                    'section'   => $resource['section'],
                    'modul'     => $resource['module'],
                ), $resource['parent']);
            }

            $rowset = $modelResource->select($where);
            $parent = $rowset->current();
        } else {
            $parent = 0;
        }

        // Add resource
        $resourceId = $modelResource->add($data, $parent);
        if (!$resourceId) {
            $message[] = sprintf(
                'Resource "%s" is not created.',
                $data['name']
            );
            return false;
        }

        if (isset($resource['privileges'])) {
            foreach ($resource['privileges'] as $name => $privilege) {
                $data = array(
                    'resource'  => $resourceId,
                    'module'    => $resource['module'],
                    'name'      => $name,
                    'title'     => isset($privilege['title'])
                                   ? $privilege['title'] : $name,
                );
                $row = $modelPrivilege->createRow($data);
                $row->save();
                if (!$row->id) {
                    $message[] = sprintf(
                        'Privilege "%s" is not created.',
                        implode('-', array_values($data))
                    );
                    return false;
                }
                if (isset($privilege['access'])) {
                    foreach ($privilege['access'] as $role => $rule) {
                        AclHandler::addRule(
                            $rule,
                            $role,
                            $resource['section'],
                            $resource['module'],
                            $resourceId,
                            $name
                        );
                    }
                }
            }
        // Insert access rules
        } elseif (isset($resource['access'])) {
            foreach ($resource['access'] as $role => $rule) {
                AclHandler::addRule(
                    $rule,
                    $role,
                    $resource['section'],
                    $resource['module'],
                    $resourceId
                );
            }
        }

        return true;
    }

    /**
     * Update ACL resource
     *
     * @param array $resource
     * @param array $message
     * @return bool
     */
    protected function updateResource($resource, &$message)
    {
        $modelResource = Pi::model('acl_resource');
        $modelRule = Pi::model('acl_rule');
        $modelPrivilege = Pi::model('acl_privilege');

        $resourceRow = $modelResource->find($resource['id']);
        if (!$resourceRow) {
            $message[] = sprintf('Resource %d is not found.', $resource['id']);
            return false;
        }
        $resourceRow->title = $resource['title'];
        $resourceRow->save();

        $privileges_exist = $modelPrivilege->select(array(
            'resource' => $resource['id'],
        ));
        $privileges_new = isset($resource['privileges'])
            ? $resource['privileges'] : array();
        $privileges_remove = array();
        foreach ($privileges_exist as $privilege) {
            if (isset($privileges_new[$privilege['name']])) {
                $privilege->title =
                    $privileges_new[$privilege['name']]['title'];
                $privilege->save();
                unset($privileges_new[$privilege['name']]);
                continue;
            } else {
                $privileges_remove[$privilege['id']] = $privilege['name'];
            }
        }
        if (!empty($privileges_remove)) {
            $modelPrivilege->delete(array(
                'id' => array_keys($privileges_remove),
            ));
            $modelRule->delete(array(
                'privilege' => array_values($privileges_remove),
                'resource' => $resource['id'],
            ));
        }
        foreach ($privileges_new as $name => $privilege) {
            $data = array(
                'resource'  => $resource['id'],
                'module'    => $resource['module'],
                'name'      => $name,
                'title'     => isset($privilege['title'])
                               ? $privilege['title'] : $name,
            );
            $row = $modelPrivilege->createRow($data);
            $row->save();
            if (!$row->id) {
                $message[] = sprintf(
                    'Privilege "%s" is not created.',
                    implode('-', array_values($data))
                );
                return false;
            }
            if (isset($privilege['access'])) {
                foreach ($privilege['access'] as $role => $rule) {
                    AclHandler::addRule(
                        $rule,
                        $role,
                        $resource['section'],
                        $resource['module'],
                        $resource['id'],
                        $name
                    );
                }
            }
        }

        return true;
    }

    /**
     * Delete ACL resource
     *
     * @param int|Pi\Application\Model\Acl\Resource $resource
     * @param array $message
     * @return bool
     */
    protected function deleteResource($resource, &$message = null)
    {
        $modelResource = Pi::model('acl_resource');
        $modelRule = Pi::model('acl_rule');
        $modelPrivilege = Pi::model('acl_privilege');

        if (is_scalar($resource)) {
            $resourceRow = $modelResource->find($resource);
        } else {
            $resourceRow = $modelResource->find($resource->id);
        }
        $resources = array();
        $children = $modelResource->getChildren($resourceRow);
        foreach ($children as $row) {
            $resources[] = array(
                'section'   => $row->section,
                'module'    => $row->module,
                'resource'  => $row->id,
            );
        }
        $modelResource->remove($resourceRow, true);
        foreach ($resources as $data) {
            $modelRule->delete($data);

            unset($data['section']);
            $modelPrivilege->delete($data);
        }

        return true;
    }
}
