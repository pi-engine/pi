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
use Pi\Acl\Acl as AclHandler;
use Pi\Application\Bootstrap\Resource\AdminMode;

/**
 * Permission controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class PermController extends ActionController
{
    /**
     * Get roles of a section
     *
     * @param string $section
     * @param string $role
     * @return array
     */
    protected function getRoles($section, &$role)
    {
        $rowset = Pi::model('acl_role')->select(array('section' => $section));
        $roles = array();
        foreach ($rowset as $row) {
            if ('admin' == $row->name) {
                continue;
            }
            $roles[$row->name] = __($row->title);
            if (!$role) {
                $role = $row->name;
            }
        }

        return $roles;
    }

    /**
     * Form managed module permission components
     */
    public function indexAction()
    {
        $module = $this->params('name', 'system');
        $section = $this->params('section', 'front');
        $role = $this->params('role');

        $roles = $this->getRoles($section, $role);

        $rowset = Pi::model('acl_resource')
            ->select(array(
                'module'    => $module,
                'section'   => $section,
                'type'      => 'system'
            ));
        if ($rowset->count()) {
            Pi::service('i18n')->load('module/' . $module . ':permission');
        }
        $resources = array();
        foreach ($rowset as $row) {
            $resources[$row->id] = array(
                'section'   => $section,
                'name'      => $module,
                'resource'  => $row->id,
                'title'     => __($row->title),
                'perm'      => null,
                'direct'    => 0,
            );
        }
        ksort($resources);

        if ($resources) {
            $rowset = Pi::model('acl_rule')
                ->select(array(
                    'role'      => $role,
                    'section'   => $section,
                    'resource'  => array_keys($resources),
                    'module'    => $module
                ));
            foreach ($rowset as $row) {
                $perm = $row->deny ? -1 : 1;
                $resources[$row->resource]['perm'] = $perm;
                $resources[$row->resource]['direct'] = $perm;
            }
            $aclHandler = new AclHandler($section);
            $aclHandler->setModule($module)->setRole($role);
            foreach (array_keys($resources) as $key) {
                $resources[$key]['perm'] = $aclHandler->checkAccess(
                    $resources[$key]['resource']
                ) ? 1 : -1;
            }
        }

        $this->view()->assign('name', $module);
        $this->view()->assign('role', $role);
        $this->view()->assign('section', $section);
        $this->view()->assign('title', __('Module permissions'));
        $this->view()->assign('roles', $roles);
        $this->view()->assign('resources', array_values($resources));
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
        $direct     = (int) $this->params('direct');
        $perm       = (int) $this->params('perm');
        $section    = $this->params('section');
        $module     = $this->params('name');

        // Remove permission
        if (empty($direct)) {
            AclHandler::removeRule($role, $section, $module, $resource);
        } else {
            AclHandler::setRule($perm, $role, $section, $module, $resource);
        }

        Pi::registry('moduleperm')->flush();

        $aclHandler = new AclHandler($section);
        $aclHandler->setModule($module)->setRole($role);
        if (in_array(
            $section,
            array('admin', 'module-admin', 'module-manage')
        )) {
            $aclHandler->setDefault(false);
            Pi::registry('navigation')->flush();
        } else {
            $aclHandler->setDefault(true);
        }
        $perm = $aclHandler->checkAccess($resource) ? 1 : -1;

        $status = 1;
        $message = __('Permission assigned successfully.');
        return array(
            'status'    => $status,
            'message'   => $message,
            'data'      => array(
                'perm'      => $perm,
                'direct'    => $direct,
                'section'   => $section,
                'name'      => $module,
                'resource'  => $resource,
            ),
        );
    }

    /**
     * For front permssion assignment
     */
    public function frontAction()
    {
        $section = 'front';
        $role = $this->params('role');

        $roles = $this->getRoles($section, $role);

        $modules = Pi::registry('modulelist')->read();
        foreach (array_keys($modules) as $key) {
            $modules[$key]['section'] = 'module-' . $section;
            $modules[$key]['resource'] = $key;
            $modules[$key]['perm'] = null;
            $modules[$key]['direct'] = 0;
        }
        $rowset = Pi::model('acl_rule')->select(array(
            'role'      => $role,
            'section'   => 'module-' . $section,
            'resource'  => array_keys($modules),
            'module'    => array_keys($modules)
        ));
        foreach ($rowset as $row) {
            $perm = $row->deny ? -1 : 1;
            $modules[$row->resource]['perm'] = $perm;
            $modules[$row->resource]['direct'] = $perm;
        }

        $modulesAllowed = Pi::registry('moduleperm')
            ->read($section, $role);
        if (null !== $modulesAllowed && is_array($modulesAllowed)) {
            foreach (array_keys($modules) as $key) {
                $modules[$key]['perm'] = in_array($key, $modulesAllowed)
                    ? 1 : -1;
            }
        }

        //$this->view()->assign('name', $module);
        $this->view()->assign('role', $role);
        $this->view()->assign('section', $section);
        $this->view()->assign('title', __('Module permissions'));
        $this->view()->assign('roles', $roles);
        $this->view()->assign('modules', array_values($modules));

    }

    /**
     * Get module blocks to which a role has access
     *
     * @return int[]
     */
    public function blocksAction()
    {
        //$section = 'front';
        $role = $this->params('role');
        $name = $this->params('name');

        $model = Pi::model('block');
        $select = $model->select()
            ->where(array('module' => $name))->order(array('id ASC'));
        $rowset = $model->selectWith($select);
        $blocks = array();
        foreach ($rowset as $row) {
            $blocks[$row->id] = array(
                'section'       => 'block',
                'name'          => $name,
                'resource'      => $row->id,
                'title'         => $row->title,
                'perm'          => 1,
                'direct'        => 0,
            );
        }
        if ($blocks) {
            $rowset = Pi::model('acl_rule')->select(array(
                'role'      => $role,
                'section'   => 'block',
                'resource'  => array_keys($blocks)
            ));
            $checked = array();
            foreach ($rowset as $row) {
                $perm = $row->deny ? -1 : 1;
                $blocks[$row->resource]['perm'] = $perm;
                $blocks[$row->resource]['direct'] = $perm;
                $checked[] = $row->resource;
            }
            //$remaining = array_diff(array_keys($blocks), $checked);
            $remaining = array_keys($blocks);
            if ($remaining) {
                $acl = new AclHandler('block');
                $acl->setRole($role);
                $where = Pi::db()->where(array('resource' => $remaining));
                $blocksDenied = $acl->getResources($where, false);
                foreach ($blocksDenied as $id) {
                    $blocks[$id]['perm'] = -1;
                }
            }
        }

        $blockList = array_values($blocks);

        return $blockList;
    }

    /**
     * For admin permission assignment
     */
    public function adminAction()
    {
        $section = AdminMode::MODE_ADMIN;
        $role = $this->params('role');

        $roles = $this->getRoles($section, $role);

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
            $rowset = Pi::model('acl_rule')->select(array(
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

        //$this->view()->assign('name', $module);
        $this->view()->assign('role', $role);
        $this->view()->assign('section', $section);
        $this->view()->assign('title', __('System permissions'));
        $this->view()->assign('roles', $roles);
        $this->view()->assign('modules', $moduleList);
    }
}
