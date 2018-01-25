<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Demo\Controller\Admin;

use Module\Demo\Form\RouteFilter;
use Module\Demo\Form\RouteForm;
use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * Feature list:
 *
 *  1. List of routes
 *  2. Edit a route
 *  5. Delete a route
 */
class RouteController extends ActionController
{
    protected $routeColumns
        = [
            'id', 'module', 'name', 'title', 'type', 'section', 'priority',
        ];

    /**
     * List of routes
     */
    public function indexAction()
    {
        $module = $this->getModule();
        $select = Pi::model('route')->select()
            ->where(['module' => $module, 'custom' => 1])
            ->order(['priority DESC']);
        $rowset = Pi::model('route')->selectWith($select);
        $routes = [];
        foreach ($rowset as $row) {
            $data             = $row->data;
            $routes[$row->id] = [
                'id'     => $row->id,
                'name'   => $row->name,
                'module' => $row->module,
                'class'  => $data['type'],
            ];
        }

        $this->view()->assign('routes', $routes);
        $this->view()->assign('title', _a('Route list'));
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
                $values['data'] = [
                    'type' => $values['type'],
                ];
                unset($values['type']);
                $values['active']  = 1;
                $values['section'] = 'front';
                $values['custom']  = 1;
                unset($values['id']);

                $row = Pi::model('route')->createRow($values);
                $row->save();
                if ($row->id) {
                    $message = _a('Route data saved successfully.');
                    Pi::registry('route')->flush();

                    //$this->view()->setTemplate(false);
                    $this->redirect()->toRoute('', ['action' => 'index']);
                    return;
                } else {
                    $message = _a('Route data not saved.');
                }
            } else {
                $message = _a('Invalid data, please check and re-submit.');
            }
        } else {
            $form = new RouteForm('route');
            $form->setAttribute(
                'action',
                $this->url('', ['action' => 'add'])
            );
            $form->setData([
                'module'  => $module,
                'section' => 'front',
            ]);
            $message = '';
        }


        $this->view()->assign('form', $form);
        $this->view()->assign('title', _a('Add a route'));
        $this->view()->assign('message', $message);
        $this->view()->setTemplate('route-edit');
    }

    /**
     * AJAX action for adding a custom route
     */
    public function addsaveAction()
    {
        $status  = 1;
        $message = '';
        $route   = [];

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
            $values['data'] = [
                'type' => $values['type'],
            ];
            unset($values['type']);
            $values['active'] = 1;
            unset($values['id']);

            $row = Pi::model('route')->createRow($values);
            $row->save();
            if ($row->id) {
                $message = _a('Route added successfully.');
                $route   = [
                    'id'     => $row->id,
                    'title'  => $row->title,
                    'edit'   => $this->url(
                        '',
                        ['action' => 'edit', 'id' => $row->id]
                    ),
                    'delete' => $this->url(
                        '',
                        ['action' => 'delete', 'id' => $row->id]
                    ),
                ];
                Pi::registry('route')->flush();
            } else {
                $message = _a('Route data not saved.');
                $status  = 0;
            }
        } else {
            $messages = $form->getMessages();
            $message  = [];
            foreach ($messages as $key => $msg) {
                $message[$key] = array_values($msg);
            }
            $status = -1;
        }

        return [
            'status'  => $status,
            'message' => $message,
            'route'   => $route,
        ];
    }

    /**
     * Edit a custom route
     */
    public function editAction()
    {
        if ($this->request->isPost()) {
            $data = $this->request->getPost();

            $id   = $data['id'];
            $row  = Pi::model('route')->find($id);
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
                $values['data'] = [
                    'type' => $values['type'],
                ];
                unset($values['type']);
                $row->assign($values);
                $row->save();
                $message = _a('Route data saved successfully.');
                $this->redirect()->toRoute('', ['action' => 'index']);
                return;
            } else {
                $message = _a('Invalid data, please check and re-submit.');
            }
        } else {
            $id           = $this->params('id');
            $row          = Pi::model('route')->find($id);
            $data         = $row->toArray();
            $data['type'] = $data['data']['type'];
            unset($data['data']);
            $form = new RouteForm('route');
            $form->setData($data);
            $form->setAttribute(
                'action',
                $this->url('', ['action' => 'edit'])
            );
            $message = '';
        }

        $this->view()->assign('form', $form);
        $this->view()->assign('title', _a('Route edit'));
        $this->view()->assign('message', $message);
        $this->view()->setTemplate('route-edit');
    }

    /**
     * AJAX for editing a page
     */
    public function editsaveAction()
    {
        $status  = 1;
        $message = '';
        $route   = [];

        $data = $this->request->getPost();

        $id   = $data['id'];
        $row  = Pi::model('route')->find($id);
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
            $values['data'] = [
                'type' => $values['type'],
            ];
            unset($values['type']);
            $row->assign($values);
            $row->save();

            $message = _a('Route data saved successfully.');
            $route   = [
                'id'     => $id,
                'title'  => $row->title,
                'edit'   => $this->url(
                    '',
                    ['action' => 'edit', 'id' => $id]
                ),
                'delete' => $this->url('', [
                    'action' => 'delete',
                    'id'     => $id,
                ]),
            ];
        } else {
            $messages = $form->getMessages();
            $message  = [];
            foreach ($messages as $key => $msg) {
                $message[$key] = array_values($msg);
            }
            $status = -1;
        }

        return [
            'status'  => $status,
            'message' => $message,
            'route'   => $route,
        ];
    }

    /**
     * AJAX for deleting a route
     *
     */
    public function deleteAction()
    {
        $id  = $this->params('id');
        $row = Pi::model('route')->find($id);
        // Only custom or cloned pages are allowed to delete
        if ($row && $row->custom) {
            $row->delete();
        }
        $this->redirect()->toRoute('', ['action' => 'index']);
        //return 1;
    }
}
