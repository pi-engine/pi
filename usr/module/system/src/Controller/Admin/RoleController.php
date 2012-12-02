<?php
/**
 * System admin role controller
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
 * @package         Module\System
 * @subpackage      Controller
 * @version         $Id$
 */

namespace Module\System\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Zend\Db\Sql\Expression;

/**
 * Feature list:
 *  1. List of roles with inheritance
 *  2. Add a role
 *  3. Clone a role and its inheritance and rules
 *  4. Edit a role
 *  5. Activate/deactivate a role
 *  6. Delete a role
 */
class RoleController extends ActionController
{
    /**
     * List of roles
     */
    public function indexAction()
    {
        // Get module list
        $moduleSet = Pi::model('module')->select(array());
        $modules = array(
            ''  => __('Custom'),
        );
        foreach ($moduleSet as $row) {
            $modules[$row->name] = $row->title;
        }

        $rowsetRole = Pi::model('acl_role')->select(array());
        $rowsetInherit = Pi::model('acl_inherit')->select(array());

        $roles = array();
        foreach ($rowsetRole as $role) {
            // Role data: name, title, description, active, module
            $data = $role->toArray();
            $data['module'] = $modules[$role->module];
            $roles[$role->name]['role'] = $data;
            // Get all ancestors of the role from role registry
            $rels = Pi::service('registry')->role->read($role->name);
            foreach ($rels as $rel) {
                // Add dependence (direct and inherited), will be indicated with "V" marker
                if (!isset($roles[$role->name]['inherit'][$rel])) {
                    $roles[$role->name]['inherit'][$rel] = -1;
                }
            }
        }
        // Add direct dependence, i.e. parent dependence
        foreach ($rowsetInherit as $rel) {
            $roles[$rel->child]['inherit'][$rel->parent] = $rel->id;
        }
        // Validate dependence matrix
        foreach (array_keys($roles) as $role) {
            foreach (array_keys($roles) as $parent) {
                // Remove self link
                if (!empty($roles[$parent]['inherit'][$role]) || $role == $parent) {
                    $roles[$role]['inherit'][$parent] = 0;
                }
            }
        }

        $this->view()->assign('roles', $roles);
        $this->view()->assign('title', __('Role inheritance'));

        $this->view()->setTemplate('role-list');
    }

    /**
     * Add a custom role
     */
    public function addAction()
    {
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form = new RoleForm('role');
            $form->setInputFilter(new RoleFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                foreach (array_keys($values) as $key) {
                    if (!in_array($key, $this->roleColumns)) {
                        unset($values[$key]);
                    }
                }
                unset($values['id']);
                unset($values['module']);

                $row = Pi::model('acl_role')->createRow($values);
                $row->save();
                if ($row->id) {
                    Pi::service('registry')->role->flush();
                    $message = __('Role data saved successfully.');
                    $this->jump(array('action' => 'index'), $message);
                    return;
                } else {
                    $message = __('Role data not saved.');
                }
            } else {
                $message = __('Invalid data, please check and re-submit.');
            }
        } else {
            $form = new RoleForm('role');
            $form->setAttribute('action', $this->url('', array('action' => 'add')));
            $message = '';
        }

        $this->view()->assign('title', __('Add a role'));
        $this->view()->assign('message', $message);
        $this->view()->setTemplate('role-edit');
    }

    /**
     * Clone a role
     */
    public function cloneAction()
    {
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form = new RoleForm('role');
            $form->setInputFilter(new RoleFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                foreach (array_keys($values) as $key) {
                    if (!in_array($key, $this->roleColumns)) {
                        unset($values[$key]);
                    }
                }
                unset($values['id']);

                $row = Pi::model('acl_role')->createRow($values);
                $row->save();
                if ($row->id) {
                    $message = __('Role data saved successfully.');

                    $fromRow = Pi::model('acl_role')->find($data['from']);
                    // Clone inheritance
                    $inherits = Pi::model('acl_inherit')->select(array('child' => $fromRow->name))->toArray();
                    foreach ($inherits as $inherit) {
                        $data = array(
                            'child'     => $row->name,
                            'parent'    => $inherit['parent'],
                        );
                        Pi::model('acl_inherit')->createRow($data)->save();
                    }
                    // Clone access rules
                    $rules = Pi::model('acl_rule')->select(array('role' => $fromRow->name))->toArray();
                    foreach ($rules as $rule) {
                        $data = $rule;
                        unset($data['id']);
                        Pi::model('acl_rule')->createRow($data)->save();
                    }

                    Pi::service('registry')->role->flush();

                    $this->jump(array('action' => 'index'), $message);
                    return;
                } else {
                    $message = __('Role data not saved.');
                }
            } else {
                $message = __('Invalid data, please check and re-submit.');
            }
        } else {
            $form = new RoleForm('role');
            $form->setAttribute('action', $this->url('', array('action' => 'clone')));
            $form->setData(array(
                'from'  => $this->params('from'),
            ));
            $message = '';
        }

        $this->view()->assign('title', __('Clone a role'));
        $this->view()->assign('message', $message);
        $this->view()->setTemplate('role-edit');
    }
}
