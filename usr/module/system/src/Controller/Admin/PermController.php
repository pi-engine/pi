<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Controller\Admin;

use Pi;
use Module\System\Controller\ComponentController;

/**
 * Permission controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class PermController extends ComponentController
{
    /**
     * Get exceptions for permission check
     *
     * @return string
     */
    public function permissionException()
    {
        return 'assign';
    }

    /**
     * Section permissions
     */
    public function indexAction()
    {
        $module = $this->params('name', $this->moduleName('system'));

        if (!$this->permission($module, 'permission')) {
            return;
        }
        
        $this->view()->setTemplate('perm');
        $this->view()->assign('name', $module);
    }

    /**
     * List of resources for perm assignment
     *
     * @return array
     */
    public function resourcesAction() 
    {
        $section = _get('section') ? : 'front';
        $module = _get('name') ?: $this->moduleName('system');
        $roles = Pi::registry('role')->read($section);

        if (!$this->permission($module, 'permission')) {
            return;
        }

        $resourceList = array(
            'front' => array(
                'global'    => array(),
                'module'    => array(),
                'block'     => array(),
            ),
            'admin' => array(
                'global'    => array(),
                'module'    => array(),
            ),
        );
        $resources = $resourceList[$section];
        Pi::service('i18n')->load('module/' . $module . ':permission');
        
        $resources['global']['module-access'] = array(
            'section'   => $section,
            'module'    => $module,
            'resource'  => 'module-access',
            'title'     => _a('Module access'),
            'roles'     => array(),
        );
        $resources['global']['module-admin'] = array(
            'section'   => $section,
            'module'    => $module,
            'resource'  => 'module-admin',
            'title'     => _a('Module admin'),
            'roles'     => array(),
        );

        /*
        $rowset = Pi::model('permission_resource')->select(array(
            'module'    => $module,
            'section'   => $section,
        ));
        */
        $select = Pi::model('permission_resource')->select()->where(array(
            'module'    => $module,
            'section'   => $section,
        ))->order('id ASC');
        $rowset = Pi::model('permission_resource')->selectWith($select);
        $callback = '';
        foreach ($rowset as $row) {
            if ('custom' == $row['type']) {
                $callback = $row['name'];
                continue;
            }
            $resources['module'][$row['name']] = array(
                'section'   => $section,
                'module'    => $module,
                'resource'  => $row['name'],
                'title'     => _a($row['title']),
                'roles'     => array(),
            );
        }
        if ($callback) {
            $callbackClass      = $callback;
            $callbackHandler    = new $callbackClass($module);
            $resourceCustom     = $callbackHandler->getResources();
            foreach ($resourceCustom as $name => $title) {
                $key = $name;
                $resources['module'][$key] = array(
                    'section'   => $section,
                    'module'    => $module,
                    'title'     => $title,
                    'resource'  => $key,
                    'roles'     => array(),
                );
            }
        }

        // Load block resources
        if ('front' == $section) {
            $model = Pi::model('block');
            $select = $model->select()
                ->where(array('module' => $module))->order(array('id ASC'));
            $rowset = $model->selectWith($select);
            foreach ($rowset as $row) {
                $key = 'block-' . $row['id'];
                $resources['block'][$key] = array(
                    'section'   => 'front',
                    'module'    => $module,
                    'resource'  => $key,
                    'title'     => $row['title'],
                    'roles'     => array(),
                );
            }
        }
 

        $rowset = Pi::model('permission_rule')->select(array(
            'module'    => $module,
            'section'   => $section,
        ));
        $rules = array();
        foreach ($rowset as $row) {
            $rules[$row['resource']][$row['role']] = 1;
        }

        $roleList = array_fill_keys(array_keys($roles), 0);
        $resourceData = array();
        foreach ($resources as $type => &$typeList) {
            foreach ($typeList as $name => &$resource) {
                $perms = array();
                foreach ($roleList as $role => $val) {
                    $item = array(
                        'name'  => $role,
                        'value' => isset($rules[$name][$role]) ? $rules[$name][$role] : $val
                    );
                    $perms[] = $item;
                }
                $resource['roles'] = $perms;
                $resourceData[$type][] = $resource;
            }
        }

        return array(
            'resources' => $resourceData,
            'roles'     => $roles
        );
    }

    /**
     * AJAX: Assign permission to a role upon a module managed resource
     *
     * @return array
     */
    public function assignAction()
    {
        $role       = $this->params('role');
        $resource   = $this->params('resource');
        $section    = $this->params('section');
        $module     = $this->params('name', $this->moduleName('system'));
        $op         = $this->params('op', 'grant');
        //$all        = $this->params('all', ''); // role, resource

        $model = Pi::model('permission_rule');
        // Grant/revoke permissions on all roles for a resource
        if ('_all' == $role) {
            $where = array(
                'section'   => $section,
                'module'    => $module,
                'resource'  => $resource,
            );
            if ('revoke' == $op) {
                $model->delete($where);
            } else {
                $roles = Pi::registry('role')->read($section);
                $rowset = $model->select($where);
                foreach ($rowset as $row) {
                    unset($roles[$row['role']]);
                }
                foreach (array_keys($roles) as $role) {
                    $data = $where + array('role' => $role);
                    $row = $model->createRow($data);
                    $row->save();
                }
            }
        // Grant/revoke permissions on all resources for a role
        } elseif ('_all' == $resource) {
            $where = array(
                'section'   => $section,
                'module'    => $module,
                'role'      => $role,
            );
            $model->delete($where);
            if ('revoke' == $op) {
                //$model->delete($where);
            } else {
                $resources = array();
                $callback = '';
                $rowset = Pi::model('permission_resource')->select(array(
                    'section'   => $section,
                    'module'    => $module,
                ));
                foreach ($rowset as $row) {
                    if ('custom' == $row['type']) {
                        $callback = $row['name'];
                        continue;
                    }
                    $resources[$row['name']] = 1;
                }
                $rowset = $model->select($where);
                foreach ($rowset as $row) {
                    unset($resources[$row['resource']]);
                }

                // Load global resources
                $resources['module-access'] = $resources['module-admin'] = 1;
                /*
                if ('admin' == $section) {
                    $resources['module-manage'] = 1;
                }
                */

                // Load module defined resources
                if ($callback && is_subclass_of(
                        $callback,
                        'Pi\Application\Api\AbstractApi'
                    )
                ) {
                    $callbackClass      = $callback;
                    $callbackHandler    = new $callbackClass($module);
                    $resourceCustom     = $callbackHandler->getResources();
                    foreach (array_keys($resourceCustom) as $name) {
                        $resources[$name] = 1;
                    }
                }

                // Load block resources
                if ('front' == $section) {
                    // Add all block resources
                    $modelBlock = Pi::model('block');
                    $select = $modelBlock->select()
                        ->where(array('module' => $module));
                    $rowset = $modelBlock->selectWith($select);
                    foreach ($rowset as $row) {
                        $resources['block-' . $row['id']] = 1;
                    }

                    /*
                    // Load module defined resources
                    if ($callback && is_subclass_of(
                            $callback,
                            'Pi\Application\Api\AbstractApi'
                        )
                    ) {
                        $callbackHandler = new $callback($module);
                        $resourceCustom = $callbackHandler->getResources();
                        foreach (array_keys($resourceCustom) as $name) {
                            $resources[$name] = 1;
                        }
                    }
                    */
                }

                foreach (array_keys($resources) as $resource) {
                    $data = $where + array('resource' => $resource);
                    $row = $model->createRow($data);
                    $row->save();
                }
            }
        // Grant/revoke permission on a resource for a role
        } else {
            $row = $model->select(compact(
                'section',
                'module',
                'resource',
                'role'
            ))->current();
            if ($row && 'revoke' == $op) {
                $row->delete();
            } elseif (!$row && 'grant' == $op) {
                $row = $model->createRow(compact(
                    'section',
                    'module',
                    'resource',
                    'role'
                ));
                $row->save();
            }
        }

        // Flush caches
        Pi::registry('block')->flush();
        Pi::registry('navigation')->flush();

        $status = 1;
        $message = _a('Permission assigned successfully.');
        return array(
            'status'    => $status,
            'message'   => $message,
        );
    }
}
