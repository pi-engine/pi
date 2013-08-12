<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Page\Controller\Admin;

use Pi;
use Pi\Mvc\Controller\ActionController;
use Module\Page\Form\PageForm;
use Module\Page\Form\PageFilter;

/**
 * Index action controller
 */
class IndexController extends ActionController
{
    protected $pageColumns = array(
        'id', 'name', 'title', 'slug', 'content', 'markup', 'active',
        'user', 'time'
    );

    /**
     * List of custom pages
     */
    public function indexAction()
    {
        $model = $this->getModel('page');
        $rowset = $model->select(array());
        $pages = array(
            'active'    => array(),
            'inactive'  => array(),
        );
        foreach ($rowset as $row) {
            $page = array(
                'id'    => $row->id,
                'name'  => $row->name,
                'title' => $row->title,
                'slug'  => $row->slug,
            );
            $page['url'] = $this->url('.page', $page);
            if ($row->active) {
                $pages['active'][] = $page;
            } else {
                $pages['inactive'][] = $page;
            }
        }

        $this->view()->assign('pages', $pages);
        $this->view()->assign('title', __('Page list'));
        $this->view()->setTemplate('page-list');
    }

    /**
     * Add a custom page
     */
    public function addAction()
    {
        $markup = 'text';
        //$module = $this->getModule();
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $markup = $data['markup'];
            $form = new PageForm('page-form', $markup);
            $form->setInputFilter(new PageFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                foreach (array_keys($values) as $key) {
                    if (!in_array($key, $this->pageColumns)) {
                        unset($values[$key]);
                    }
                }
                if (empty($values['name'])) {
                    $values['name'] = null;
                }
                if (empty($values['slug'])) {
                    $values['slug'] = null;
                }
                $values['active'] = 1;
                $values['user'] = Pi::service('user')->getUser()->id;
                $values['time_created'] = time();
                unset($values['id']);

                $row = $this->getModel('page')->createRow($values);
                $row->save();
                if ($row->id) {
                    if ($row->name) {
                        $this->setPage($row->name, $row->title);
                    }
                    Pi::registry('page')->clear($this->getModule());
                    $message = __('Page data saved successfully.');
                    $this->jump(array('action' => 'index'), $message);
                    return;
                } else {
                    $message = __('Page data not saved.');
                }
            } else {
                $message = __('Invalid data, please check and re-submit.');
            }
        } else {
            $markup = $this->params('type', 'text');
            $form = new PageForm('page-form', $markup);
            $form->setAttribute('action',
                                $this->url('', array('action' => 'add')));
            $message = '';
        }

        $this->view()->assign('markup', $markup);
        $this->view()->assign('form', $form);
        $this->view()->assign('title', __('Add a page'));
        $this->view()->assign('message', $message);
        $this->view()->setTemplate('page-add');
    }

    /**
     * AJAX action for adding a custom page
     */
    public function addsaveAction()
    {
        $status     = 1;
        $message    = '';
        $route      = array();

        $data = $this->request->getPost();
        $form = new PageForm('page-form', $data['markup']);
        $form->setInputFilter(new PageFilter);
        $form->setData($data);
        if ($form->isValid()) {
            $values = $form->getData();
            foreach (array_keys($values) as $key) {
                if (!in_array($key, $this->pageColumns)) {
                    unset($values[$key]);
                }
            }
            if (empty($values['name'])) {
                $values['name'] = null;
            }
            if (empty($values['slug'])) {
                $values['slug'] = null;
            }
            $values['active'] = 1;
            $values['user'] = Pi::service('user')->getUser()->id;
            $values['time_created'] = time();
            unset($values['id']);

            $row = $this->getModel('page')->createRow($values);
            $row->save();
            if ($row->id) {
                if ($row->name) {
                    $this->setPage($row->name, $row->title);
                }
                $message = __('Page added successfully.');
                $page = array(
                    'id'            => $row->id,
                    'title'         => $row->title,
                    'url'           => $this->url('page', $values),
                    'edit'          => $this->url('', array(
                        'action' => 'edit',
                        'id' => $row->id
                    )),
                    'delete'        => $this->url('', array(
                        'action' => 'delete',
                        'id' => $row->id
                    )),
                );
                Pi::registry('page')->clear($this->getModule());
            } else {
                $message = __('Page data not saved.');
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
            'page'      => $page,
        );
    }

    /**
     * Edit a custom page
     */
    public function editAction()
    {
        if ($this->request->isPost()) {
            $data = $this->request->getPost();

            $id = $data['id'];
            $row = $this->getModel('page')->find($id);
            $form = new PageForm('page-form', $row->markup);
            $form->setInputFilter(new PageFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                foreach (array_keys($values) as $key) {
                    if (!in_array($key, $this->pageColumns)) {
                        unset($values[$key]);
                    }
                }
                if (empty($values['name'])) {
                    $values['name'] = null;
                }
                if (empty($values['slug'])) {
                    $values['slug'] = null;
                }
                $pageSet = array();
                if ($row->name != $values['name']) {
                    $pageSet = array(
                        'remove'    => $row->name,
                        'set'       => array($values['name'],
                                             $values['title']),
                    );
                }
                $values['time_updated'] = time();
                $row->assign($values);
                $row->save();
                if ($pageSet) {
                    if (!empty($pageSet['set'])) {
                        $this->setPage($pageSet['set']);
                    }
                    if (!empty($pageSet['remove'])) {
                        $this->removePage($pageSet['remove']);
                    }
                }
                Pi::registry('page')->clear($this->getModule());
                $message = __('Page data saved successfully.');
                $this->jump(array('action' => 'index'), $message);
                return;
            } else {
                $message = __('Invalid data, please check and re-submit.');
            }
        } else {
            $id = $this->params('id');
            $row = $this->getModel('page')->find($id);
            $data = $row->toArray();
            $form = new PageForm('page-form', $row->markup);
            $form->setData($data);
            $form->setAttribute('action',
                                $this->url('', array('action' => 'edit')));
            $message = '';
        }

        $this->view()->assign('form', $form);
        $this->view()->assign('title', __('Page edit'));
        $this->view()->assign('message', $message);
        $this->view()->setTemplate('page-edit');
    }

    /**
     * AJAX for editing a page
     */
    public function editsaveAction()
    {
        $status     = 1;
        $message    = '';
        $page       = array();

        $data = $this->request->getPost();

        $id = $data['id'];
        $row = $this->getModel('page')->find($id);
        $form = new PageForm('page-form', $row->markup);
        $form->setInputFilter(new PageFilter);
        $form->setData($data);
        if ($form->isValid()) {
            $values = $form->getData();
            foreach (array_keys($values) as $key) {
                if (!in_array($key, $this->pageColumns)) {
                    unset($values[$key]);
                }
            }
            if (empty($values['name'])) {
                $values['name'] = null;
            }
            if (empty($values['slug'])) {
                $values['slug'] = null;
            }
            $pageSet = array();
            if ($row->name != $values['name']) {
                $pageSet = array(
                    'remove'    => $row->name,
                    'set'       => array($values['name'], $values['title']),
                );
            }
            $values['time_updated'] = time();
            $row->assign($values);
            $row->save();
            if ($pageSet) {
                if (!empty($pageSet['set'])) {
                    $this->setPage($pageSet['set']);
                }
                if (!empty($pageSet['remove'])) {
                    $this->removePage($pageSet['remove']);
                }
            }
            $message = __('Page data saved successfully.');
            $page = array(
                'id'            => $id,
                'title'         => $row->title,
            );
            Pi::registry('page')->clear($this->getModule());
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
            'page'      => $page,
        );
    }

    /**
     * AJAX for deleting a page
     *
     */
    public function deleteAction()
    {
        $status     = 1;
        $message    = __('Page deleleted successfaully.');

        $id = $this->params('id');
        $row = $this->getModel('page')->find($id);
        if ($row) {
            $row->delete();
            if ($row->name) {
                $this->removePage($row->name);
            }
            Pi::registry('page')->clear($this->getModule());
        }
        //$this->redirect()->toRoute('', array('action' => 'index'));

        return array(
            'status'    => $status,
            'message'   => $message,
        );
    }

    /**
     * AJAX for activate/deactivate a page
     *
     */
    public function activateAction()
    {
        $status     = 1;
        $message    = __('Page updated successfully.');

        $id = $this->params('id');
        $row = $this->getModel('page')->find($id);
        if ($row) {
            $row->active = $row->active ? 0 : 1;
            $row->save();
            Pi::registry('page')->clear($this->getModule());
        }
        //$this->redirect()->toRoute('', array('action' => 'index'));

        return array(
            'status'    => $status,
            'message'   => $message,
        );
    }

    /**
     * Add page settings to system
     *
     * @param string $name
     * @param string $title
     * @return int
     */
    protected function setPage($name, $title)
    {
        $page = array(
            'section'       => 'front',
            'module'        => $this->getModule(),
            'controller'    => 'index',
            'action'        => $name,
            'title'         => $title,
            'block'         => 1,
            'custom'        => 0,
        );
        $row = Pi::model('page')->createRow($page);
        $row->save();

        return $row->id;
    }

    /**
     * Remove from system page settings
     *
     * @param stinr $name
     * @return int
     */
    protected function removePage($name)
    {
        $where = array(
            'section'       => 'front',
            'module'        => $this->getModule(),
            'controller'    => 'index',
            'action'        => $name,
        );
        $count = Pi::model('page')->delete($where);

        return $count;
    }
}
