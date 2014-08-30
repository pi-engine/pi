<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
    /**
     * List of custom pages
     */
    public function indexAction()
    {
        $model  = $this->getModel('page');
        $select = $model->select()->order(array('active DESC', 'nav_order ASC', 'id DESC'));
        $rowset = $model->selectWith($select);
        $pages  = array();
        $menu   = array();
        foreach ($rowset as $row) {
            $page           = $row->toArray();
            $page['url']    = $this->url('page', $page);
            if ($page['nav_order'] && $page['active']) {
                $menu[] = $page;
            } else {
                $pages[] = $page;
            }
        }
        $pages = array_merge($menu, $pages);

        $this->view()->assign('pages', $pages);
        $this->view()->assign('title', _a('Page list'));
        $this->view()->setTemplate('page-list');
    }

    /**
     * Add a custom page
     */
    public function addAction()
    {
        //$markup = 'text';
        //$module = $this->getModule();
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $markup = $data['markup'];
            /*
            // Set slug
            if (!empty($data['slug'])) {
                $data['slug'] = Pi::api('text', 'page')->slug($data['slug']);
            }
            // Set name
            if (!empty($data['name'])) {
                $data['name'] = Pi::api('text', 'page')->name($data['name']);
            }
            */
            // Set form
            $form = new PageForm('page-form', $markup);
            $form->setInputFilter(new PageFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                /*
                foreach (array_keys($values) as $key) {
                    if (!in_array($key, $this->pageColumns)) {
                        unset($values[$key]);
                    }
                }
                */
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

                /*
                // Set seo_title
                $title = ($values['seo_title']) ? $values['seo_title'] : $values['title'];
                $values['seo_title'] = Pi::api('text', 'page')->title($title);
                // Set seo_keywords
                $keywords = ($values['seo_keywords']) ? $values['seo_keywords'] : $values['title'];
                $values['seo_keywords'] = Pi::api('text', 'page')->keywords($keywords);
                // Set seo_description
                $description = ($values['seo_description']) ? $values['seo_description'] : $values['title'];
                $values['seo_description'] = Pi::api('text', 'page')->description($description);
                */

                // Save
                $id = Pi::api('api', $this->getModule())->add($values);
                //$row = $this->getModel('page')->createRow($values);
                //$row->save();
                if ($id) {
                    /*
                    if ($row->name) {
                        $this->setPage($row->name, $row->title);
                    }
                    Pi::registry('page')->clear($this->getModule());
                    */
                    $message = _a('Page data saved successfully.');
                    return $this->jump(array('action' => 'index'), $message);
                } else {
                    $message = _a('Page data not saved.');
                }
            } else {
                $message = _a('Invalid data, please check and re-submit.');
            }
        } else {
            $markup = $this->params('type', 'text');
            $form = new PageForm('page-form', $markup);
            $form->setAttribute(
                'action',
                $this->url('', array('action' => 'add'))
            );
            if ('phtml' == $markup) {
                $template = $this->params('template');
                if ($template) {
                    $form->setData(array(
                        'content'   => $template,
                    ));
                }
            }
            $message = '';
        }

        $this->view()->assign('markup', $markup);
        $this->view()->assign('form', $form);
        $this->view()->assign('title', _a('Add a page'));
        $this->view()->assign('message', $message);
        $this->view()->setTemplate('page-add');
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
            /*
            // Set slug
            if (!empty($data['slug'])) {
                $data['slug'] = Pi::api('text', 'page')->slug($data['slug']);
            }
            // Set name
            if (!empty($data['name'])) {
                $data['name'] = Pi::api('text', 'page')->name($data['name']);
            }
            */
            // Set form
            $form = new PageForm('page-form', $row->markup);
            $form->setInputFilter(new PageFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                /*
                foreach (array_keys($values) as $key) {
                    if (!in_array($key, $this->pageColumns)) {
                        unset($values[$key]);
                    }
                }
                */
                if (empty($values['name'])) {
                    $values['name'] = null;
                }
                if (empty($values['slug'])) {
                    $values['slug'] = null;
                }
                if (!$values['name'] || $row->name != $values['name']) {
                    $this->removePage($row->name);
                }
                if ($values['name']) {
                    $this->setPage($values['name'], $values['title']);
                }
                $values['time_updated'] = time();

                /*
                // Set seo_title
                $title = ($values['seo_title']) ? $values['seo_title'] : $values['title'];
                $values['seo_title'] = Pi::api('text', 'page')->title($title);
                // Set seo_keywords
                $keywords = ($values['seo_keywords']) ? $values['seo_keywords'] : $values['title'];
                $values['seo_keywords'] = Pi::api('text', 'page')->keywords($keywords);
                // Set seo_description
                $description = ($values['seo_description']) ? $values['seo_description'] : $values['title'];
                $values['seo_description'] = Pi::api('text', 'page')->description($description);
                */

                // Save
                $row->assign($values);
                $row->save();
                Pi::registry('page')->clear($this->getModule());
                Pi::service('cache')->flush('module', $this->getModule());
                $message = _a('Page data saved successfully.');
                return $this->jump(array('action' => 'index'), $message);
            } else {
                $message = _a('Invalid data, please check and re-submit.');
            }
        } else {
            $id = $this->params('id');
            $row = $this->getModel('page')->find($id);
            $data = $row->toArray();
            $form = new PageForm('page-form', $row->markup);
            $form->setData($data);
            $form->setAttribute(
                'action',
                $this->url('', array('action' => 'edit'))
            );
            $message = '';
        }

        $this->view()->assign('form', $form);
        $this->view()->assign('title', _a('Page edit'));
        $this->view()->assign('message', $message);
        $this->view()->setTemplate('page-edit');
    }

    /**
     * Delete a page
     *
     */
    public function deleteAction()
    {
        $id = $this->params('id');
        $row = $this->getModel('page')->find($id);
        if ($row) {
            if ($row->name) {
                $this->removePage($row->name);
            }
            $row->delete();
            Pi::registry('page')->clear($this->getModule());
            Pi::registry('page', $this->getModule())->flush();
            Pi::registry('nav', $this->getModule())->flush();
        }

        return $this->jump(
            array('action' => 'index'),
            _a('Page deleted successfully.')
        );
    }

    /**
     * Activate/deactivate a page
     *
     */
    public function activateAction()
    {
        $id = $this->params('id');
        $row = $this->getModel('page')->find($id);
        if ($row) {
            $row->active = $row->active ? 0 : 1;
            $row->save();
            Pi::registry('page')->clear($this->getModule());
        }
        Pi::registry('page', $this->getModule())->flush();
        Pi::registry('nav', $this->getModule())->flush();

        return $this->jump(
            array('action' => 'index'),
            _a('Page updated successfully.')
        );
    }

    /**
     * Add pages to navigation menu
     *
     */
    public function menuAction()
    {
        $orders = $this->params('order');
        $model = $this->getModel('page');
        foreach ($orders as $id => $value) {
            $model->update(
                array('nav_order' => (int) $value),
                array('id' => (int) $id)
            );
        }
        Pi::registry('nav', $this->getModule())->flush();

        return $this->jump(
            array('action' => 'index'),
            _a('Page navigation menu updated successfully.')
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
        if (!$name) {
            return;
        }
        $page = array(
            'section'       => 'front',
            'module'        => $this->getModule(),
            'controller'    => 'index',
            'action'        => $name,
        );
        $row = Pi::model('page')->select($page)->current();
        if ($row) {
            $row->title = $title;
        } else {
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
        }
        $row->save();
        Pi::registry('page', $this->getModule())->flush();

        return $row->id;
    }

    /**
     * Remove from system page settings
     *
     * @param string $name
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
