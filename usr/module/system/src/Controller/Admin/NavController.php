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
use Pi\Mvc\Controller\ActionController;
use Module\System\Form\NavFilter;
use Module\System\Form\NavForm;
use Module\System\Form\NavPageFilter;
use Module\System\Form\NavPageForm;

/**
 * Navigaiton controller
 *
 * Feature list:
 *
 *  - Select global front/admin navigation
 *  - Clone a system navigation
 *  - Add/Rename/Delete a custom navigation
 *  - Edit/Clone a custom navigation
 *  - Navigation data manipulation
 *    - Add
 *    - Edit
 *    - Rename
 *    - Move
 *    - Delete
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class NavController extends ActionController
{
    /**
     * Columns of navigation model
     * @var string[]
     */
    protected $navColumns = array(
        'id', 'name', 'title', 'section', 'active'
    );

    /**
     * Columns of page model
     * @var string[]
     */
    protected $pageColumns = array(
        'id',
        'navigation', 'name', 'label', 'module', 'controller', 'action',
        'route', 'uri', 'target', 'resource', 'visible'
    );

    /**
     * Set up front/admin global navigations
     */
    public function indexAction()
    {
        $modules = Pi::registry('module')->read();
        $modules[''] = array('title' => __('Custom'));

        $navList = array();
        $navGlobal = Pi::model('config')->select(array(
            'module'    => 'system',
            'name'      => 'nav_front',
        ))->current()->value;
        $rowset = Pi::model('navigation')->select(array(
            'section'   => 'front',
            'active'    => 1
        ));
        foreach ($rowset as $row) {
            $navList[] = $row->toArray();
        }

        $this->view()->assign('navList', array_values($navList));
        $this->view()->assign('title', __('Navigation list'));
        $this->view()->assign('navGlobal', $navGlobal);
        //$this->view()->setTemplate('nav-select');
    }

    /**
     * List of navigations to manage
     */
    public function listAction()
    {
        $modules = Pi::registry('module')->read();
        $modules[''] = array('title' => __('Custom'));

        $navGlobal = array(
            'front' => Pi::config('nav_front', ''),
            'admin' => Pi::config('nav_admin', ''),
        );
        $navModule = array(
            'front' => array(),
            'admin' => array(),
        );
        $navCustom = array(
            'front' => array(),
            'admin' => array(),
        );

        $rowset = Pi::model('navigation')->select(
            array('module <> ?' => 'system', 'active' => 1)
        );

        foreach ($rowset as $row) {
            if ($row->module) {
                $navModule[$row->section][$row->module][] = array(
                    'name'  => $row->name,
                    'title' => $row->title,
                );
            } else {
                $navCustom[$row->section][] = array(
                    'name'  => $row->name,
                    'title' => $row->title,
                );
            }
        }

        $this->view()->assign('navGlobal', $navGlobal);
        $this->view()->assign('navCustom', $navCustom);
        $this->view()->assign('navModule', $navModule);
        $this->view()->assign('modules', $modules);
        $this->view()->assign('title', __('Navigation list'));
        //$this->view()->setTemplate('nav-list');
    }

    /**
     * AJAX to apply navigations
     *
     * @return array Result pair of status and message
     */
    public function applyAction()
    {
        $nav_front = $this->params()->fromPost('nav_front');
        $nav_admin = $this->params()->fromPost('nav_admin');

        $row = Pi::model('config')->select(array(
            'module'    => 'system',
            'name'      => 'nav_front',
        ))->current();
        $row->value = $nav_front;
        $row->save();

        $row = Pi::model('config')->select(array(
            'module'    => 'system',
            'name'      => 'nav_admin',
        ))->current();
        $row->value = $nav_admin;
        $row->save();

        Pi::registry('config')->clear('system');

        $result = array(
            'status'    => 1,
            'message'   => __('Navigation set up successfully.'),
        );

        return $result;
    }

    /**
     * Add a custom navigation
     */
    public function addAction()
    {
        if ($this->request->isPost()) {
            $status = 1;
            $nav = array();
            $data = $this->request->getPost();
            $form = new NavForm('nav');
            $form->setInputFilter(new NavFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                foreach (array_keys($values) as $key) {
                    if (!in_array($key, $this->navColumns)) {
                        unset($values[$key]);
                    }
                }
                $values['active'] = 1;
                unset($values['id']);

                $row = Pi::model('navigation')->createRow($values);
                $row->save();
                if ($row->id) {
                    $message = __('Navigation saved successfully.');
                    $nav = $row->toArray();
                } else {
                    $status = 0;
                    $message = __('Navigation data not saved.');
                }
            } else {
                $status = -1;
                $messages = $form->getMessages();
                $message = array();
                foreach ($messages as $key => $msg) {
                    $message[$key] = array_values($msg);
                }
            }

            return array(
                'status'        => $status,
                'message'       => $message,
                'navigation'    => $nav,
            );
        } else {
            $form = new NavForm('nav');
            $form->setData(
                array('section' => $this->params('section', 'front'))
            );
            $form->setAttribute(
                'action',
                $this->url('', array('action' => 'add'))
            );
        }
        $this->view()->assign('form', $form);
        $this->view()->assign('title', __('Add a navigation'));
        $this->view()->setTemplate('system:component/form-popup');
    }

    /**
     * Clone a navigation
     *
     * @return array|void
     */
    public function cloneAction()
    {
        if ($this->request->isPost()) {
            $status = 1;
            $nav = array();
            $data = $this->request->getPost();
            $form = new NavForm('nav');
            $form->setInputFilter(new NavFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                $parent = $values['parent'];
                foreach (array_keys($values) as $key) {
                    if (!in_array($key, $this->navColumns)) {
                        unset($values[$key]);
                    }
                }
                $values['active'] = 1;
                unset($values['id']);
                unset($values['module']);

                $row = Pi::model('navigation')->createRow($values);
                $row->save();
                if ($row->id) {
                    $message = __('Navigation saved successfully.');
                    $this->cloneNode($parent, $row->name);

                    $nav = $row->toArray();
                } else {
                    $status = 0;
                    $message = __('Navigation data not saved.');
                }
            } else {
                $status = -1;
                $messages = $form->getMessages();
                $message = array();
                foreach ($messages as $key => $msg) {
                    $message[$key] = array_values($msg);
                }
            }

            return array(
                'status'        => $status,
                'message'       => $message,
                'navigation'    => $nav,
            );
        } else {
            $parent = $this->params('name');
            $parentRow = Pi::model('navigation')->find($parent, 'name');

            $form = new NavForm('nav');
            $form->setData(array(
                'section'   => $parentRow->section,
                'title'     => $parentRow->title,
            ));
            $form->setAttribute(
                'action',
                $this->url('', array('action' => 'clone'))
            );
            $form->add(array(
                'name'          => 'parent',
                'attributes'    => array(
                    'type'  => 'hidden',
                    'value' => $parentRow->name,
                )
            ));
        }
        $this->view()->assign('form', $form);
        $this->view()->assign('title', __('Clone navigation'));
        $this->view()->setTemplate('system:component/form-popup');
    }

    /**
     * AJAX method to delete a nav
     *
     * @return array Result pair of status and message
     */
    public function deleteAction()
    {
        $nav = $this->params('name');
        $navigation = array(
            'front' => Pi::config('nav_front', ''),
            'admin' => Pi::config('nav_admin', ''),
        );
        if ($nav == $navigation['front'] || $nav == $navigation['admin']) {
            $result = array(
                'status'    => 0,
                'message'   =>
                    __('The navigation is in use and not allowed to delete.'),
            );
            return $result;
        }
        $row = Pi::model('navigation_node')->find($nav, 'navigation');
        if ($row) {
            $row->delete();
        }
        $row = Pi::model('navigation')->find($nav, 'name');
        $row->delete();
        $result = array(
            'status'    => 1,
            'message'   => __('The navigation is deleted successfully.'),
        );

        return $result;
    }

    /**
     * Clone a nav's pages
     *
     * @param string $parent
     * @param string $nav
     * @return bool
     */
    protected function cloneNode($parent, $nav)
    {
        $data = Pi::registry('navigation')->read($parent) ?: array();
        $node = array(
            'navigation'    => $nav,
            'data'          => $data,
        );
        $row = Pi::model('navigation_node')->createRow($node);
        $row->save();

        return true;
    }

    /**
     * Navigation page data manipulation
     */
    public function dataAction()
    {
        $nav = $this->params('name');
        $readonly = $this->params('readonly');

        //$row = Pi::model('navigation_node')->find($nav, 'navigation');
        //$pages = $row->data;

        $pages = Pi::registry('navigation')->read($nav) ?: array();
        $plainList = array();
        $no     = 1;
        $depth  = 0;
        $pid    = 0;
        foreach ($pages as $key => &$node) {
            $id = (string) $no;
            $no++;
            $this->transformNode($node, $plainList, $id, $pid, $depth);
        }
        $pageList = array_values($plainList);

        /*
        $navTree = '';
        foreach ($pages as $key => $element) {
            $navTree .= $this->renderElement($element);
        }
        */

        $form = new NavPageForm('nav-page');
        $form->setData(array(
            'navigation'    => $nav,
            'visible'       => '1',
        ));
        $form->setAttribute(
            'action',
            $this->url('', array('action' => 'update'))
        );

        if ($readonly) {
            $title = __('View of navigation details: %s');
        } else {
            $title = __('Navigation data edit: %s');
        }
        $navigation = Pi::model('navigation')->find($nav, 'name');
        $nav = $navigation->toArray();

        $title = sprintf($title, $navigation->title);
        $this->view()->assign('readonly', $readonly);
        $this->view()->assign('navigation', $nav);
        //$this->view()->assign('navTree', $navTree);
        $this->view()->assign('pages', $pageList);
        $this->view()->assign('form', $form);
        $this->view()->assign('title', $title);
        $this->view()->setTemplate('nav-data');
    }

    /**
     * Transform navigation node data
     *
     * @param array $node
     * @param array $plainList
     * @param int $id
     * @param int $pid
     * @param int $depth
     * @return void
     */
    protected function transformNode(&$node, &$plainList, $id, $pid, $depth)
    {
        $node['id']     = $id;
        $node['pid']    = $pid;
        $node['depth']  = $depth;
        if (!isset($node['visible'])) {
            $node['visible'] = 1;
        }
        $plainList[$id] = $node;
        if (isset($node['pages'])) {
            unset($plainList[$id]['pages']);
            $depth++;
            $no = 1;
            foreach ($node['pages'] as $key => &$page) {
                $cid = $id . '-' . $no;
                $no++;
                $this->transformNode($page, $plainList, $cid, $id, $depth);
            }
        }

        return;
    }

    /**
     * AJAX method to validate page node data
     *
     * @return array Result pair of status and message
     */
    public function pageAction()
    {
        $status     = 1;
        $message    = '';
        $data = $this->request->getPost();
        $form = new NavPageForm('nav-page');
        $form->setInputFilter(new NavPageFilter);
        $form->setData($data);
        if (!$form->isValid()) {
            $status = -1;
            $messages = $form->getMessages();
            $message = array();
            foreach ($messages as $key => $msg) {
                $message[$key] = array_values($msg);
            }
        }

        return array(
            'status'        => $status,
            'message'       => $message,
        );
    }

    /**
     * AJAX: Update DB to store manipulated data
     *
     * Re-create the whole navigation
     *
     * @return array Result pair of status and message
     */
    public function updateAction()
    {
        $status = 1;
        $message = __('Navigation data saved successfully.');

        $model  = Pi::model('navigation');
        $nav    = $this->request->getPost('name');
        $row    = $model->find($nav, 'name');
        if (!$row) {
            $status = 0;
            $message = __('Navigation not found.');
        } elseif ($row->module) {
            $status = 0;
            $message =
                __('Only custom navigations are allowed to manipulate.');
        }
        if (!$status) {
            return array(
                'status'    => $status,
                'message'   => $message,
            );
        }

        $pages = $this->request->getPost('pages');
        $pages = $this->canonizePages($pages);

        $row = Pi::model('navigation_node')->find($nav, 'navigation');
        if (!$row) {
            $row = Pi::model('navigation_node')->createRow(
                array('navigation' => $nav, 'data' => $pages)
            );
        }
        $row->data = $pages;
        $row->save();

        Pi::registry('navigation')->flush();

        return array(
            'status'    => $status,
            'message'   => $message,
        );
    }

    /**
     * Canonize navigation page data
     *
     * @param array $pages
     * @return array
     */
    protected function canonizePages($pages)
    {
        $temp = array();
        foreach ($pages as $page) {
            $id = $page['id'];
            unset($page['id'], $page['depth']);
            if (!empty($page['visible'])) {
                unset($page['visible']);
            }
            $temp[$id] = $page;
        }

        // Set up key container
        $keys = array_fill_keys(array_keys($temp), 1);
        // Look up node list to append child node to its parent,
        // until no child node is left in container
        $registered = array();
        do {
            foreach (array_keys($keys) as $key) {
                $item =& $temp[$key];
                // Has parent
                if (isset($item['pid'])) {
                    $parentKey = $item['pid'];
                    //unset($item['pid']);
                    // Register to parent
                    if (isset($temp[$parentKey])) {
                        if (!isset($temp[$parentKey]['pages'])) {
                            $temp[$parentKey]['pages'] = array();
                            $temp[$parentKey]['pages'][] =& $item;
                            $registered[$key] = 1;
                        } elseif (!isset($registered[$key])) {
                            $temp[$parentKey]['pages'][] =& $item;
                            $registered[$key] = 1;
                        }
                        // To reactivate parent
                        $keys[$parentKey] = 1;
                    }
                }
                // Remove node from container
                unset($keys[$key]);
            }
        } while ($keys);

        // Fetch formuated nodes
        $list = array();
        foreach ($temp as $key => $node) {
            if (!empty($node['pid'])) {
                continue;
            }
            $list[$key] = $node;
        }

        return $list;
    }
}
