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
use Pi\Mvc\Controller\ActionController;
//use Pi\Paginator\Paginator;
use Module\System\Form\RoleForm;
use Module\System\Form\RoleFilter;

/**
 * Role controller
 *
 * Feature list:
 *
 *  1. List of roles with inheritance
 *  2. Add a role
 *  3. Clone a role and its rules
 *  4. Edit a role
 *  5. Activate/deactivate a role
 *  6. Delete a role
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class RoleController extends ActionController
{
    /**
     * Columns for role model
     *
     * @var string[]
     */
    protected $roleColumns = array(
        'id', 'section', 'custom', 'active', 'name', 'title'
    );

    /**
     * Get role model
     *
     * @return Pi\Application\Model\Model
     */
    protected function model()
    {
        return Pi::model('role');
    }

    /**
     * Get role list
     *
     * Data structure
     *
     *  - role
     *    - id
     *    - name
     *    - title
     *    - active
     *    - custom
     *    - section
     *
     * @param string $section
     * @return array
     */
    protected function getRoles($section = '')
    {
        $roles = array();

        $select = $this->model()->select();
        $select->order('title ASC');
        if ($section) {
            $select->where(array('section' => $section));
        }
        $rowset = $this->model()->selectWith($select);
        foreach ($rowset as $row) {
            $role = $row->toArray();
            $role['active'] = (int) $role['active'];
            $role['custom'] = (int) $role['custom'];
            $roles[$row['name']] =$role;
        }

        return $roles;
    }

    /**
     * Entrance template
     *
     * @return void
     */
    public function indexAction()
    {
        $this->view()->setTemplate('role');
    }

    /**
     * List of roles
     */
    public function listAction()
    {
        $roles = $this->getRoles();
        /*
        if (isset($roles['guest'])) {
            unset($roles['guest']);
        }
        */
        /*
        $rowset = Pi::model('user_role')->count(
            array('role' => array_keys($roles)),
            'role'
        );
        $count = array();
        foreach ($rowset as $row) {
            $count[$row['role']] = (int) $row['count'];
        }
        */

        return array(
            'roles'    => array_values($roles),
        );
    }

    /**
     * Add a custom role
     *
     * @return void|array
     */
    public function addAction()
    {
        if ($this->request->isPost()) {
            $data = _post();
            $form = new RoleForm('role', $data['section']);
            $form->setInputFilter(new RoleFilter);
            $form->setData($data);

            $status = 1;
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

                $row = $this->model()->createRow($values);
                $row->save();
                if ($row->id) {
                    Pi::registry('role')->flush();
                    $roleData = $row->toArray();
                    $message = _a('Role data saved successfully.');
                } else {
                    $status = 0;
                    $message = _a('Role data not saved.');
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
            $form->setAttribute(
                'action',
                $this->url('', array('action' => 'add'))
            );
            $this->view()->assign('title', _a('Add a role'));
            $this->view()->assign('form', $form);
            $this->view()->setTemplate('system:component/form-popup');
        }
    }

    /**
     * Edit a role
     *
     * @return array|void
     */
    public function editAction()
    {
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form = new RoleForm('role', $data['section']);
            $form->setInputFilter(new RoleFilter);
            $form->setData($data);

            $status = 1;
            $roleData = array();
            if ($form->isValid()) {
                $values = $form->getData();
                $row = $this->model()->find($values['id']);
                $row->assign($values);
                try {
                    $row->save();
                    Pi::registry('role')->flush();
                    $roleData = $row->toArray();
                    $message = _a('Role data saved successfully.');
                } catch (\Exception $e) {
                    $status = 0;
                    $message = _a('Role data not saved.');
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
            $row = $this->model()->find($id);
            $section = $row->section;
            $data = $row->toArray();
            $form = new RoleForm('role', $section);
            $form->setAttribute(
                'action',
                $this->url('', array('action' => 'edit'))
            );
            $form->setData($data);
            $this->view()->assign('title', _a('Edit a role'));
            $this->view()->assign('form', $form);
            $this->view()->setTemplate('system:component/form-popup');
        }
    }

    /**
     * AJAX: Activate/deactivate a role
     *
     * @return array
     */
    public function activateAction()
    {
        $status = 1;
        $data = 0;
        $id = $this->params('id');
        $row = $this->model()->find($id);
        if (!$row['custom']) {
            $status = 0;
            $message =
                _a('Only custom roles are allowed to activate/deactivate.');
        } else {
            if ($row->active) {
                $row->active = 0;
            } else {
                $row->active = 1;
            }
            $data = $row->active;
            $row->save();
            Pi::registry('role')->flush();
            $message = _a('Role updated successfully.');
        }
        return array(
            'status'    => $status,
            'message'   => $message,
            'data'      => $data,
        );
    }

    /**
     * AJAX: Rename a role
     *
     * @return int
     */
    public function renameAction()
    {
        $id = $this->params('id');
        $title = $this->params('title');
        $row = $this->model()->find($id);
        $row->title = $title;
        $row->save();

        Pi::registry('role')->flush();

        return array('status' => 1);
    }

    /**
     * AJAX: Delete a role
     *
     * @return array
     */
    public function deleteAction()
    {
        $status = 1;
        $id = $this->params('id');
        $row = $this->model()->find($id);
        if (!$row['custom']) {
            $status = 0;
            $message = _a('Only custom roles are allowed to delete.');
        } else {
            Pi::model('user_role')->delete(array('role' => $row->name));
            Pi::model('permission_rule')->delete(array('role' => $row->name));
            $row->delete();
            Pi::registry('role')->flush();
            $message = _a('Role deleted successfully.');
        }

        $data = $this->getRoles($row->section);

        return array(
            'status'    => $status,
            'message'   => $message,
            'data'      => $data,
        );
    }

    /**
     * Check if a role name exists
     *
     * @return int
     */
    public function checkExistAction()
    {
        $role = _get('name');
        $row = Pi::model('role')->find($role, 'name');
        $status = $row ? 1 : 0;

        return array(
            'status' => $status
        );
    }
}
