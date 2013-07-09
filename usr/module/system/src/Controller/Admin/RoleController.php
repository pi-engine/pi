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
use Module\System\Form\RoleForm;
use Module\System\Form\RoleFilter;

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
    protected $roleColumns = array(
        'id', 'section', 'module', 'custom', 'active', 'name', 'title'
    );

    /**
     * Get role list with following structure
     *  - role
     *      - id
     *      - name
     *      - title
     *      - active
     *      - custom
     *      - module
     *      - section
     *  - inherit
     *    - all
     *    - direct
     *    - indirect
     *
     * @param string $type
     * @return array
     */
    protected function getRoles($type)
    {
        $roles = array();
        $rowsetRole = Pi::model('acl_role')->select(array('section' => $type));
        foreach ($rowsetRole as $role) {
            if ('admin' == $role->name) {
                continue;
            }
            // Role data: name, title, description, active
            $data = $role->toArray();
            $data['inherit'] = array(
                'direct'    => array(),
                'indirect'  => array(),
                'all'       => array(),
            );
            $roles[$role->name] = $data;
            // Get all ancestors of the role from role registry
            $rels = Pi::service('registry')->role->read($role->name);
            foreach ($rels as $rel) {
                // Add dependence (direct and inherited), will be indicated with "V" marker
                if ($rel != $role->name) {
                    $roles[$role->name]['inherit']['all'][] = $rel; // = -1;
                }
            }
        }

        $rowsetInherit = Pi::model('acl_inherit')->select(array('child' => array_keys($roles)));
        // Add direct dependence, i.e. parent dependence
        foreach ($rowsetInherit as $rel) {
            $roles[$rel->child]['inherit']['direct'][] = $rel->parent; // $rel->id;
        }
        $result = array();
        foreach ($roles as $key => $data) {
            $data['inherit']['indirect'] = array_diff($data['inherit']['all'], $data['inherit']['direct']);
            $result[] = $data;
        }

        return $result;
    }

    /**
     * List of roles
     */
    public function indexAction()
    {
        $type = $this->params('type', 'front');

        $roles = $this->getRoles($type);
        $this->view()->assign('type', $type);
        $this->view()->assign('roles', $roles);
        $this->view()->assign('title', __('Role list'));

        //$this->view()->setTemplate('role-list');
    }

    /**
     * Add a custom role
     */
    public function addAction()
    {
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form = new RoleForm('role', $data['section']);
            $form->setInputFilter(new RoleFilter);
            $form->setData($data);

            $status = 1;
            $message = '';
            $roleData = array();
            if ($form->isValid()) {
                $values = $form->getData();
                foreach (array_keys($values) as $key) {
                    if (!in_array($key, $this->roleColumns)) {
                        unset($values[$key]);
                    }
                }
                $values['custom'] = 1;
                unset($values['id']);
                unset($values['module']);

                $row = Pi::model('acl_role')->createRow($values);
                $row->save();
                if ($row->id) {
                    Pi::service('registry')->role->flush();
                    $roleData = $row->toArray();
                    $roleData['inherit'] = array(
                        'direct'    => array(),
                        'indirect'  => array(),
                        'all'       => array(),
                    );
                    $message = __('Role data saved successfully.');
                } else {
                    $status = 0;
                    $message = __('Role data not saved.');
                }
            } else {
                $status = 0;
                $messages = $form->getMessages();
                $message = array();
                foreach ($messages as $key => $msg) {
                    $message[$key] = array_values($msg);
                }
            }
            return array(
                'status'    => $status,
                'message'   => $message,
                'data'      => $roleData,
            );
        } else {
            $type = $this->params('type', 'front');
            $form = new RoleForm('role', $type);
            $form->setAttribute('action', $this->url('', array('action' => 'add')));
            $this->view()->assign('title', __('Add a role'));
            $this->view()->assign('form', $form);
            $this->view()->setTemplate('system:component/form-popup');
        }
    }

    /**
     * Edit a role
     */
    public function editAction()
    {
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form = new RoleForm('role', $data['section']);
            $form->setInputFilter(new RoleFilter);
            $form->setData($data);

            $status = 1;
            $message = '';
            $roleData = array();
            if ($form->isValid()) {
                $values = $form->getData();
                $row = Pi::model('acl_role')->find($values['id']);
                $row->assign($values);
                try {
                    $row->save();
                    Pi::service('registry')->role->flush();
                    $roleData = $row->toArray();
                    $message = __('Role data saved successfully.');
                } catch (\Exception $e) {
                    $status = 0;
                    $message = __('Role data not saved.');
                }
            } else {
                $status = 0;
                $messages = $form->getMessages();
                $message = array();
                foreach ($messages as $key => $msg) {
                    $message[$key] = array_values($msg);
                }
            }
            return array(
                'status'    => $status,
                'message'   => $message,
                'data'      => $roleData,
            );
        } else {
            $id = $this->params('id');
            $row = Pi::model('acl_role')->find($id);
            $section = $row->section;
            $data = $row->toArray();
            $form = new RoleForm('role', $section);
            $form->setAttribute('action', $this->url('', array('action' => 'edit')));
            $form->setData($data);
            $this->view()->assign('title', __('Edit a role'));
            $this->view()->assign('form', $form);
            $this->view()->setTemplate('system:component/form-popup');
        }
    }

    /**
     * Add/remove an inheritance
     */
    public function inheritAction()
    {
        $status = 1;
        $message = '';
        $data = array();

        $child = $this->params('child');
        $parent = $this->params('parent');
        $add = $this->params('add');

        $roleChild = Pi::model('acl_role')->find($child, 'name');

        if ($add) {
            $row = Pi::model('acl_inherit')->createRow(array(
                'child'     => $child,
                'parent'    => $parent,
            ));
            try {
                $row->save();
                $parents = Pi::model('acl_role')->getAncestors($parent);
                if ($parents) {
                    Pi::model('acl_inherit')->delete(array(
                        'child'     => $child,
                        'parent'    => $parents,
                    ));
                }
                $message = __('Role inherited successfully.');
            } catch (\Exception $e) {
                $status = 0;
                $message = $e->getMessage();
            }
        } else {
            try {
                Pi::model('acl_inherit')->delete(array(
                    'child'     => $child,
                    'parent'    => $parent,
                ));
                $message = __('Role uninherited successfully.');
            } catch (\Exception $e) {
                $status = 0;
                $message = $e->getMessage();
            }
        }
        Pi::service('registry')->role->flush();
        $data = $this->getRoles($roleChild->section);

        return array(
            'status'    => $status,
            'message'   => $message,
            'data'      => $data,
        );
    }

    /**
     * Activate/deactivate a role
     */
    public function activateAction()
    {
        $status = 1;
        $message = '';
        $data = 0;
        $id = $this->params('id');
        $row = Pi::model('acl_role')->find($id);
        if ($row->module) {
            $status = 0;
            $message = __('Only custom roles are allowed to activate/deactivate.');
        } else {
            if ($row->active) {
                $row->active = 0;
            } else {
                $row->active = 1;
            }
            $data = $row->active;
            $row->save();
            Pi::service('registry')->role->flush();
            $message = __('Role updated successfully.');
        }
        return array(
            'status'    => $status,
            'message'   => $message,
            'data'      => $data,
        );
    }

    /**
     * Rename a role
     */
    public function renameAction()
    {
        $id = $this->params('id');
        $title = $this->params('title');
        $row = Pi::model('acl_role')->find($id);
        $row->title = $title;
        $row->save();

        return 1;
    }

    /**
     * Delete a role
     */
    public function deleteAction()
    {
        $status = 1;
        $message = '';
        $id = $this->params('id');
        $row = Pi::model('acl_role')->find($id);
        if ($row->module) {
            $status = 0;
            $message = __('Only custom roles are allowed to delete.');
        } else {
            Pi::model('acl_inherit')->delete(array('child' => $row->name));
            Pi::model('acl_inherit')->delete(array('parent' => $row->name));
            Pi::model('acl_rule')->delete(array('role' => $row->name));
            $row->delete();
            Pi::service('registry')->role->flush();
            $message = __('Role deleted successfully.');
        }

        $data = $this->getRoles($row->section);

        return array(
            'status'    => $status,
            'message'   => $message,
            'data'      => $data,
        );
    }
}
