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
        $module = $this->params('name', 'system');
        // Load all active roles of current section
        $roles = array(
            'front' => Pi::registry('role')->read('front'),
            'admin' => Pi::registry('role')->read('admin'),
        );

        $resources = array(
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
        Pi::service('i18n')->load('module/' . $module . ':permission');
        foreach (array('front', 'admin') as $section) {
            $resources[$section]['global']['module-access'] = array(
                'section'   => $section,
                'module'    => $module,
                'resource'  => 'module-access',
                'title'     => __('Module access'),
                'roles'     => array(),
            );
            $resources[$section]['global']['module-admin'] = array(
                'section'   => $section,
                'module'    => $module,
                'resource'  => 'module-admin',
                'title'     => __('Module admin'),
                'roles'     => array(),
            );
        }
        // Load module defined resources
        $rowset = Pi::model('permission_resource')->select(array(
            'module'    => $module,
        ));
        $callback = '';
        foreach ($rowset as $row) {
            if ('custom' == $row['type']) {
                $callback = $row['name'];
                continue;
            }
            $resources[$row['section']]['module'][$row['name']] = array(
                'section'   => $section,
                'module'    => $module,
                'resource'  => $row['name'],
                'title'     => __($row['title']),
                'roles'     => array(),
            );
        }

        // Load module custom resources
        if ($callback
            && is_subclass_of(
                $callback,
                'Pi\Application\AbstractModuleAwareness'
            )
        ) {
            $callbackHandler = new $callback($module);
            $resourceCustom = $callbackHandler->getResources();
            foreach ($resourceCustom as $name => $title) {
                $resource = compact('section', 'module', 'name', 'title');
                $resource['roles'] = array();

                $resources['front']['module'][$name] = $resource;
                //$resourceList[] = $name;
            }
        }
        // Load block resources
        $model = Pi::model('block');
        $select = $model->select()
            ->where(array('module' => $module))->order(array('id ASC'));
        $rowset = $model->selectWith($select);
        foreach ($rowset as $row) {
            $key = 'block-' . $row['id'];
            $resources['front']['block'][$key] = array(
                'section'   => 'block',
                'module'    => $module,
                'resource'  => $key,
                'title'     => $row['title'],
                'roles'     => array(),
            );
        }

        $rowset = Pi::model('permission_rule')->select(array(
            'module'    => $module,
        ));
        $rules = array();
        foreach ($rowset as $row) {
            $rules[$row['section']][$row['resource']][$row['role']] = 1;
        }

        $roleList = array();
        foreach ($roles as $section => $rList) {
            $roleList[$section] = array_fill_keys(array_keys($rList), 0);
        }

        $resourceData = array();
        foreach ($resources as $section => &$sectionList) {
            foreach ($sectionList as $type => &$typeList) {
                foreach ($typeList as $name => &$resource) {
                    if (isset($rules[$section][$name])) {
                        $resource['roles'] = array_merge(
                            $roleList[$section],
                            $rules[$section][$name]
                        );
                    } else {
                        $resource['roles'] = $roleList[$section];
                    }
                    $resourceData[$section][$type][] = $resource;
                }
            }
        }

        $moduleList = Pi::registry('modulelist')->read('active');
        $modules = array();
        foreach ($moduleList as $name => $list) {
            $modules[$name] = $list['title'];
        }

        d($roles);
        d($modules);
        d($resourceData);
        $this->view()->setTemplate('perm');
        $this->view()->assign('name', $module);
        $this->view()->assign('title', __('Module permissions'));
        $this->view()->assign('roles', $roles);
        $this->view()->assign('modules', $modules);
        $this->view()->assign('resources', $resourceData);
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
        $module     = $this->params('name');
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
            if ('revoke' == $op) {
                $model->delete($where);
            } else {
                $resources = array();
                $rowset = Pi::model('permission_resource')->select(array(
                    'section'   => $section,
                    'module'    => $module,
                ));
                foreach ($rowset as $row) {
                    $resources[$row['name']] = 1;
                }
                $rowset = $model->select($where);
                foreach ($rowset as $row) {
                    unset($resources[$row['resource']]);
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

        $status = 1;
        $message = __('Permission assigned successfully.');
        return array(
            'status'    => $status,
            'message'   => $message,
        );
    }
}
