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
use Pi\Application\Bootstrap\Resource\AdminMode;

/**
 * Permission controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class PermController extends ActionController
{
    /**
     * Section permissions
     */
    public function indexAction()
    {
        $module = $this->params('name', 'system');
        $section = 'front';
        // Load all active roles of current section
        $roles = Pi::registry('role')->read($section);

        Pi::service('i18n')->load('module/' . $module . ':permission');
        $resources = array(
            'module'    => array(),
            'callback'  => array(),
            'block'     => array(),
        );
        $resourceList = array();
        $resources['module']['module-access'] = = array(
            'section'   => $section,
            'module'    => $module,
            'resource'  => 'module-access',
            'title'     => __('Module access'),
            'roles'     => array(),
        );
        $resources['module']['module-amin'] = = array(
            'section'   => $section,
            'module'    => $module,
            'resource'  => 'module-admin',
            'title'     => __('Module admin'),
            'roles'     => array(),
        );
        $resourceList[] = 'module-access';
        $resourceList[] = 'module-admin';
        // Load module defined resources
        $rowset = Pi::model('perm_resource')->select(array(
            'module'    => $module,
            'section'   => $section,
            'type'      => array('system', 'callback'),
        ));
        $callback = '';
        foreach ($rowset as $row) {
            if ('callback' == $row['type']) {
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
        // Load module callbacked resources
        if ($callback) {
            $callbackHandler = new $callback($module);
            $resources['callback'] = $callbackHandler->getResources();
            foreach ($resources['callback'] as $name => &$resource) {
                $resource['name']       = $name;
                $resource['module']     = $module;
                $resource['section']    = $section;
                $resource['roles']      = array();

                $resourceList[] = $name;
            }
        }
        // Load block resources
        $model = Pi::model('block');
        $select = $model->select()
            ->where(array('module' => $module))->order(array('id ASC'));
        $rowset = $model->selectWith($select);
        $blocks = array();
        foreach ($rowset as $row) {
            $key = 'block-' . $row['id'];
            $blocks[$key] = array(
                'section'   => 'block',
                'module'    => $module,
                'resource'  => $key,
                'title'     => $row['title'],
                'roles'     => array(),
            );

            $resourceList[] = $key;
        }

        if ($resourceList) {
            $rowset = Pi::model('perm_rule')->select(array(
                'section'   => $section,
                'module'    => $module,
                'resource'  => $resourceList,
            ));
            $rules = array();
            foreach ($rowset as $row) {
                $rules[$row['resource']][$row['role']] = 1;
            }
            foreach ($resources as $section => &$list) {
                foreach ($list as $name => &$resource) {
                    if (isset($rules[$name])) {
                        $resource['roles'] = $rules[$name];
                    }
                }
            }
        }

        $this->view()->assign('name', $module);
        $this->view()->assign('section', $section);
        $this->view()->assign('title', __('Module permissions'));
        $this->view()->assign('roles', $roles);
        $this->view()->assign('resources', $resources);
    }

    /**
     * For admin permission assignment
     */
    public function adminAction()
    {
        $module = $this->params('name', 'system');
        $section = AdminMode::MODE_ADMIN;
        // Load all active roles of current section
        $roles = Pi::registry('role')->read($section);

        $modulesInstalled = Pi::registry('modulelist')->read();
        foreach (array_keys($modulesInstalled) as $key) {
            $modulesInstalled[$key]['name'] = $key;
            $modulesInstalled[$key]['resource'] = $key;
            $modulesInstalled[$key]['perm'] = null;
            $modulesInstalled[$key]['direct'] = 0;
        }

        $moduleList = array();
        foreach (
            array(AdminMode::MODE_ADMIN, AdminMode::MODE_SETTING)
            as $section
        ) {
            $modules = $modulesInstalled;
            $rowset = Pi::model('perm_rule')->select(array(
                'role'      => $role,
                'section'   => 'module-' . $section,
                'resource'  => array_keys($modules),
                'module'    => array_keys($modules)
            ));
            foreach ($rowset as $row) {
                $modules[$row->resource]['section'] = 'module-' . $section;
                $perm = $row->deny ? -1 : 1;
                $modules[$row->resource]['perm'] = $perm;
                $modules[$row->resource]['direct'] = $perm;
            }

            $modulesAllowed = Pi::registry('moduleperm')
                ->read($section, $role);
            if (null !== $modulesAllowed && is_array($modulesAllowed)) {
                foreach (array_keys($modules) as $key) {
                    $modules[$key]['section'] = 'module-' . $section;
                    $modules[$key]['perm'] = in_array($key, $modulesAllowed)
                        ? 1 : -1;
                }
            }

            $moduleList[$section] = array_values($modules);
        }

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

        $model = Pi::model('perm_rule');
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

        $status = 1;
        $message = __('Permission assigned successfully.');
        return array(
            'status'    => $status,
            'message'   => $message,
        );
    }
}
