<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Demo\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\Demo\Form\RouteForm;
use Module\Demo\Form\RouteFilter;

/**
 * Feature list:
 *
 *  1. List of routes
 *  2. Edit a route
 *  5. Delete a route
 */
class RouteController extends ActionController
{
    protected $routeColumns = array(
        'id', 'module', 'name', 'title', 'type', 'section', 'priority'
    );

    /**
     * List of routes
     */
    public function indexAction()
    {
        $module = $this->getModule();
        $select = Pi::model('route')->select()
            ->where(array('module' => $module, 'custom' => 1))
            ->order(array('priority DESC'));
        $rowset = Pi::model('route')->selectWith($select);
        $routes = array();
        foreach ($rowset as $row) {
            $data = $row->data;
            $routes[$row->id] = array(
                'id'            => $row->id,
                'name'          => $row->name,
                'module'        => $row->module,
                'class'         => $data['type'],
            );
        }

        $this->view()->assign('routes', $routes);
        $this->view()->assign('title', __('Route list'));
        $this->view()->setTemplate('route-list');
    }

    /**
     * Add a custom route
     */
    public function addAction()
    {
        $module = $this->getModule();
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form = new RouteForm('route');
            $form->setInputFilter(new RouteFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                foreach (array_keys($values) as $key) {
                    if (!in_array($key, $this->routeColumns)) {
                        unset($values[$key]);
                    }
                }
                $values['data'] = array(
                    'type'  => $values['type'],
                );
                unset($values['type']);
                $values['active'] = 1;
                $values['section'] = 'front';
                $values['custom'] = 1;
                unset($values['id']);

                $row = Pi::model('route')->createRow($values);
                $row->save();
                if ($row->id) {
                    $message = __('Route data saved successfully.');
                    Pi::registry('route')->flush();

                    //$this->view()->setTemplate(false);
                    $this->redirect()->toRoute('', array('action' => 'index'));
                    return;
                } else {
                    $message = __('Route data not saved.');
                }
            } else {
                $message = __('Invalid data, please check and re-submit.');
            }
        } else {
            $form = new RouteForm('route');
            $form->setAttribute(
                'action',
                $this->url('', array('action' => 'add'))
            );
            $form->setData(array(
                'module'    => $module,
                'section'   => 'front',
            ));
            $message = '';
        }


        $this->view()->assign('form', $form);
        $this->view()->assign('title', __('Add a route'));
        $this->view()->assign('message', $message);
        $this->view()->setTemplate('route-edit');
    }

    /**
     * AJAX action for adding a custom route
     */
    public function addsaveAction()
    {
        $status     = 1;
        $message    = '';
        $route      = array();

        $data = $this->request->getPost();
        $form = new RouteForm('route');
        $form->setInputFilter(new RouteFilter);
        $form->setData($data);
        if ($form->isValid()) {
            $values = $form->getData();
            foreach (array_keys($values) as $key) {
                if (!in_array($key, $this->routeColumns)) {
                    unset($values[$key]);
                }
            }
            $values['data'] = array(
                'type'  => $values['type'],
            );
            unset($values['type']);
            $values['active'] = 1;
            unset($values['id']);

            $row = Pi::model('route')->createRow($values);
            $row->save();
            if ($row->id) {
                $message = __('Route added successfully.');
                $route = array(
                    'id'            => $row->id,
                    'title'         => $row->title,
                    'edit'          => $this->url(
                        '',
                        array('action' => 'edit', 'id' => $row->id)
                    ),
                    'delete'        => $this->url(
                        '',
                        array('action' => 'delete', 'id' => $row->id)
                    ),
                );
                Pi::registry('route')->flush();
            } else {
                $message = __('Route data not saved.');
                $status = 0;
            }
        } else {
            $messages = $form->getMessages();
            $message = array();
            foreach ($messages as $key => $msg) {
                $message[$key] = array_values($msg);
            }
            $status = -1;
        }

        return array(
            'status'    => $status,
            'message'   => $message,
            'route'     => $route,
        );
    }

    /**
     * Edit a custom route
     */
    public function editAction()
    {
        if ($this->request->isPost()) {
            $data = $this->request->getPost();

            $id = $data['id'];
            $row = Pi::model('route')->find($id);
            $form = new RouteForm('route');
            $form->setInputFilter(new RouteFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                foreach (array_keys($values) as $key) {
                    if (!in_array($key, $this->routeColumns)) {
                        unset($values[$key]);
                    }
                }
                $values['data'] = array(
                    'type'  => $values['type'],
                );
                unset($values['type']);
                $row->assign($values);
                $row->save();
                $message = __('Route data saved successfully.');
                $this->redirect()->toRoute('', array('action' => 'index'));
                return;
            } else {
                $message = __('Invalid data, please check and re-submit.');
            }
        } else {
            $id = $this->params('id');
            $row = Pi::model('route')->find($id);
            $data = $row->toArray();
            $data['type'] = $data['data']['type'];
            unset($data['data']);
            $form = new RouteForm('route');
            $form->setData($data);
            $form->setAttribute(
                'action',
                $this->url('', array('action' => 'edit'))
            );
            $message = '';
        }

        $this->view()->assign('form', $form);
        $this->view()->assign('title', __('Route edit'));
        $this->view()->assign('message', $message);
        $this->view()->setTemplate('route-edit');
    }

    /**
     * AJAX for editing a page
     */
    public function editsaveAction()
    {
        $status     = 1;
        $message    = '';
        $route      = array();

        $data = $this->request->getPost();

        $id = $data['id'];
        $row = Pi::model('route')->find($id);
        $form = new RouteForm('route');
        $form->setInputFilter(new RouteFilter);
        $form->setData($data);
        if ($form->isValid()) {
            $values = $form->getData();
            foreach (array_keys($values) as $key) {
                if (!in_array($key, $this->routeColumns)) {
                    unset($values[$key]);
                }
            }
            $values['data'] = array(
                'type'  => $values['type'],
            );
            unset($values['type']);
            $row->assign($values);
            $row->save();

            $message = __('Route data saved successfully.');
            $route = array(
                'id'            => $id,
                'title'         => $row->title,
                'edit'          => $this->url(
                    '',
                    array('action' => 'edit', 'id' => $id)
                ),
                'delete'        => $this->url('', array(
                    'action'    => 'delete',
                    'id'        => $id
                )),
            );
        } else {
            $messages = $form->getMessages();
            $message = array();
            foreach ($messages as $key => $msg) {
                $message[$key] = array_values($msg);
            }
            $status = -1;
        }

        return array(
            'status'    => $status,
            'message'   => $message,
            'route'     => $route,
        );
    }

    /**
     * AJAX for deleting a route
     *
     */
    public function deleteAction()
    {
        $id = $this->params('id');
        $row = Pi::model('route')->find($id);
        // Only custom or cloned pages are allowed to delete
        if ($row && $row->custom) {
            $row->delete();
        }
        $this->redirect()->toRoute('', array('action' => 'index'));
        //return 1;
    }
}
