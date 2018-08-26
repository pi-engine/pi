<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Page\Controller\Admin;

use Module\Page\Form\PageFilter;
use Module\Page\Form\PageForm;
use Pi;
use Pi\Filter;
use Pi\Mvc\Controller\ActionController;

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
        $select = $model->select()->order(['active DESC', 'nav_order ASC', 'id DESC']);
        $rowset = $model->selectWith($select);
        $pages  = [];
        $menu   = [];
        foreach ($rowset as $row) {
            $page        = $row->toArray();
            $page['url'] = $this->url('page', $page);
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
        if ($this->request->isPost()) {
            $data   = $this->request->getPost();
            $markup = $data['markup'];
            // Set slug
            if (!empty($data['slug'])) {
                $filter       = new Filter\Slug;
                $data['slug'] = $filter($data['slug']);
            }
            // Set name
            if (!empty($data['name'])) {
                $filter       = new Filter\Slug;
                $data['name'] = $filter($data['name']);
            }
            // Set form
            $form = new PageForm('page-form', $markup);
            $form->setInputFilter(new PageFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                if (empty($values['name'])) {
                    $values['name'] = null;
                }
                if (empty($values['slug'])) {
                    $values['slug'] = null;
                }
                $values['active']       = 1;
                $values['user']         = Pi::service('user')->getUser()->id;
                $values['time_created'] = time();
                unset($values['id']);
                // Save
                $id = Pi::api('api', $this->getModule())->add($values);
                if ($id) {
                    // Add / Edit sitemap link
                    if (Pi::service('module')->isActive('sitemap')) {
                        $loc = Pi::url($this->url('page', [
                            'slug' => $values['slug'],
                            'name' => $values['name'],
                            'id'   => $id,
                        ]));
                        Pi::api('sitemap', 'sitemap')->singleLink(
                            $loc,
                            $values['active'] ? 1 : 2,
                            $this->getModule(),
                            'page',
                            $id
                        );
                    }
                    // Set jump
                    $message = _a('Page data saved successfully.');
                    return $this->jump(['action' => 'index'], $message);
                } else {
                    $message = _a('Page data not saved.');
                }
            } else {
                $message = _a('Invalid data, please check and re-submit.');
            }
        } else {
            $markup = $this->params('type', 'html');
            $form   = new PageForm('page-form', $markup);
            $form->setAttribute(
                'action',
                $this->url('', ['action' => 'add'])
            );
            if ('phtml' == $markup) {
                $template = $this->params('template');
                if ($template) {
                    $form->setData([
                        'content' => $template,
                    ]);
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
            $id   = $data['id'];
            $row  = $this->getModel('page')->find($id);
            // Set slug
            if (!empty($data['slug'])) {
                $filter       = new Filter\Slug;
                $data['slug'] = $filter($data['slug']);
            }
            // Set name
            if (!empty($data['name'])) {
                $filter       = new Filter\Slug;
                $data['name'] = $filter($data['name']);
            }
            // Set form
            $form = new PageForm('page-form', $row->markup);
            $form->setInputFilter(new PageFilter);
            $form->setData($data);

            /**
             * Set current theme for getting custom layout
             */
            if(!empty($data['theme'])){
                $form->get('layout')->setOption('theme', $data['theme']);
            }

            if ($form->isValid()) {
                $values = $form->getData();
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
                // Save
                $row->assign($values);
                $row->save();
                Pi::registry('page')->clear($this->getModule());
                Pi::service('cache')->flush('module', $this->getModule());
                // Add / Edit sitemap link
                if (Pi::service('module')->isActive('sitemap')) {
                    $loc = Pi::url($this->url('page', [
                        'slug' => $row->slug,
                        'name' => $row->name,
                        'id'   => $row->id,
                    ]));
                    Pi::api('sitemap', 'sitemap')->singleLink($loc,
                        $row->active ? 1 : 2,
                        $this->getModule(),
                        'page',
                        $row->id
                    );
                }
                $message = _a('Page data saved successfully.');
                return $this->jump(['action' => 'index'], $message);
            } else {
                $message = _a('Invalid data, please check and re-submit.');
            }
        } else {
            $id   = $this->params('id');
            $row  = $this->getModel('page')->find($id);
            $data = $row->toArray();
            $form = new PageForm('page-form', $row->markup);
            $form->setData($data);

            /**
             * Set current theme for getting custom layout
             */
            if(!empty($data['theme'])){
                $form->get('layout')->setOption('theme', $data['theme']);
            }

            $form->setAttribute(
                'action',
                $this->url('', ['action' => 'edit'])
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
        $id  = $this->params('id');
        $row = $this->getModel('page')->find($id);
        if ($row) {
            $page = $row->toArray();
            if ($row->name) {
                $this->removePage($row->name);
            }
            $row->delete();
            Pi::registry('page')->clear($this->getModule());
            Pi::registry('page', $this->getModule())->flush();
            Pi::registry('nav', $this->getModule())->flush();
            // Clean sitemap
            if (Pi::service('module')->isActive('sitemap')) {
                $loc = Pi::url($this->url('page', $page));
                Pi::api('sitemap', 'sitemap')->remove($loc);
            }
        }

        return $this->jump(
            ['action' => 'index'],
            _a('Page deleted successfully.')
        );
    }

    /**
     * Activate/deactivate a page
     *
     */
    public function activateAction()
    {
        $id  = $this->params('id');
        $row = $this->getModel('page')->find($id);
        if ($row) {
            $row->active = $row->active ? 0 : 1;
            $row->save();
            Pi::registry('page')->clear($this->getModule());
        }
        Pi::registry('page', $this->getModule())->flush();
        Pi::registry('nav', $this->getModule())->flush();

        if (Pi::service('module')->isActive('sitemap')) {
            $loc = Pi::url($this->url('page', [
                'slug' => $row->slug,
                'name' => $row->name,
                'id'   => $row->id,
            ]));
            Pi::api('sitemap', 'sitemap')->singleLink(
                $loc,
                $row->active ? 1 : 2,
                $this->getModule(),
                'page',
                $row->id
            );
        }

        return $this->jump(
            ['action' => 'index'],
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
        $model  = $this->getModel('page');
        foreach ($orders as $id => $value) {
            $model->update(
                ['nav_order' => (int)$value],
                ['id' => (int)$id]
            );
        }
        Pi::registry('nav', $this->getModule())->flush();

        return $this->jump(
            ['action' => 'index'],
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
        $page = [
            'section'    => 'front',
            'module'     => $this->getModule(),
            'controller' => 'index',
            'action'     => $name,
        ];
        $row  = Pi::model('page')->select($page)->current();
        if ($row) {
            $row->title = $title;
        } else {
            $page = [
                'section'    => 'front',
                'module'     => $this->getModule(),
                'controller' => 'index',
                'action'     => $name,
                'title'      => $title,
                'block'      => 1,
                'custom'     => 0,
            ];
            $row  = Pi::model('page')->createRow($page);
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
        $where = [
            'section'    => 'front',
            'module'     => $this->getModule(),
            'controller' => 'index',
            'action'     => $name,
        ];
        $count = Pi::model('page')->delete($where);

        return $count;
    }
}