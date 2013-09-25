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
use Module\System\Controller\ComponentController  as ActionController;
use Pi\Application\AbstractModuleAwareness;

/**
 * Permission controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class PermController extends ActionController
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
        $section = $this->params('section', 'front');
        // Load all active roles of current section
        $roles = Pi::registry('role')->read($section);
        //vd($roles);

        Pi::service('i18n')->load('module/' . $module . ':permission');
        $resources = array(
            'module'    => array(),
            'block'     => array(),
        );
        $resourceList = array();
        $resources['global']['module-access'] = array(
            'section'   => $section,
            'module'    => $module,
            'resource'  => 'module-access',
            'title'     => __('Module access'),
            'roles'     => array(),
        );
        $resources['global']['module-admin'] = array(
            'section'   => $section,
            'module'    => $module,
            'resource'  => 'module-admin',
            'title'     => __('Module admin'),
            'roles'     => array(),
        );
        $resourceList[] = 'module-access';
        $resourceList[] = 'module-admin';
        // Load module defined resources
        $rowset = Pi::model('permission_resource')->select(array(
            'module'    => $module,
            'section'   => $section,
            //'type'      => array('system', 'custom'),
        ));
        $callback = '';
        foreach ($rowset as $row) {
            //vd($row->toArray());
            if ('custom' == $row['type']) {
                $callback = $row['name'];
                continue;
            }
            $resources['module'][$row['name']] = array(
                'section'   => $section,
                'module'    => $module,
                'resource'  => $row['name'],
                'title'     => __($row['title']),
                'roles'     => array(),
            );

            $resourceList[] = $row['name'];
        }
        //vd($callback);
        if ('front' == $section) {
            // Load module custom resources
            if ($callback
                //&& is_subclass_of($callback, 'AbstractModuleAwareness')
            ) {
                $callbackHandler = new $callback($module);
                $resourceCustom = $callbackHandler->getResources();
                foreach ($resourceCustom as $name => $title) {
                    $resource = compact('section', 'module', 'name', 'title');
                    $resource['roles'] = array();

                    $resources['module'][$name] = $resource;
                    $resourceList[] = $name;
                }
            }
            // Load block resources
            $model = Pi::model('block');
            $select = $model->select()
                ->where(array('module' => $module))->order(array('id ASC'));
            $rowset = $model->selectWith($select);
            //$blocks = array();
            foreach ($rowset as $row) {
                $key = 'block-' . $row['id'];
                $resources['block'][$key] = array(
                    'section'   => 'block',
                    'module'    => $module,
                    'resource'  => $key,
                    'title'     => $row['title'],
                    'roles'     => array(),
                );

                $resourceList[] = $key;
            }
        }

        if ($resourceList) {
            $rowset = Pi::model('permission_rule')->select(array(
                'section'   => $section,
                'module'    => $module,
                'resource'  => $resourceList,
            ));
            $rules = array();
            foreach ($rowset as $row) {
                $rules[$row['resource']][$row['role']] = 1;
            }
            foreach ($resources as $type => &$list) {
                foreach ($list as $name => &$resource) {
                    if (isset($rules[$name])) {
                        $resource['roles'] = $rules[$name];
                    }
                }
            }
        }

        vd($roles);
        vd($resources);
        $this->view()->setTemplate('perm-' . $section);
        $this->view()->assign('name', $module);
        $this->view()->assign('section', $section);
        $this->view()->assign('title', __('Module permissions'));
        $this->view()->assign('roles', $roles);
        $this->view()->assign('resources', $resources);
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
        $op         = $this->params('perm', 'grant');
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
