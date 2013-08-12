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
use Module\System\Form\PageAddForm as AddForm;
use Module\System\Form\PageAddFilter as AddFilter;
use Module\System\Form\PageEditForm as EditForm;
use Module\System\Form\PageEditFilter as EditFilter;
use Zend\Db\Sql\Expression;

/**
 * Page controller
 *
 * Feature list:
 *
 *  1. List of pages of a section and module
 *  2. Edit a page
 *  3. Add a custom page to a section and module
 *  4. Delete a custom page
 *  5. Manage blocks on a page
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class PageController extends ActionController
{
    /**
     * Columns for page model
     * @var string[]
     */
    protected $pageColumns = array(
        'id', 'section', 'module', 'controller', 'action', 'block', 'custom',
        'cache_ttl', 'cache_level', 'title'
    );

    /**
     * List of pages sorted by module and section
     */
    public function indexAction()
    {
        // Module name, default as 'system'
        $name = $this->params('name', 'system');

        // Pages of the module
        $select = Pi::model('page')->select()
            ->where(array('module' => $name))
            ->order(array('custom', 'controller', 'action', 'id'));
        $rowset = Pi::model('page')->selectWith($select);
        $sections = array(
            'front' => array(
                'title' => __('Front'),
                'pages' => array(),
            ),
            'admin' => array(
                'title' => __('Admin'),
                'pages' => array(),
            ),
            'feed'  => array(
                'title' => __('Feed'),
                'pages' => array(),
            ),
        );

        // Oragnized pages by section
        foreach ($rowset as $row) {
            //$sections[$row->section]['pages'][] = $row->toArray();
            if ($row->controller) {
                $key = $row->module;
                $key .= $row->controller ? '-' . $row->controller : '';
                $key .= $row->action ? '-' . $row->action : '';
                $title = $row->title ?: $key;
            } else {
                $key = $row->module;
                $title = sprintf(
                    __('%s module wide'),
                    $row->title ?: $row->module
                );
            }

            //$title = $row->title ?: ($key ?: __('Module wide'));
            $sections[$row->section]['pages'][] = array(
                'id'        => $row->id,
                'title'     => $title,
                'key'       => $key,
                'section'   => $row->section,
                'custom'    => $row->custom,
                'block'     => $row->block,
                //'link'      => '',
            );
        }

        /*
        // Get module list
        $moduleSet = Pi::model('module')->select(array('active' => 1));
        foreach ($moduleSet as $row) {
            $modules[$row->name] = $row->title;
        }
        $this->view()->assign('modules', $modules);
        */

        $this->view()->assign('pagesBySection', $sections);
        $this->view()->assign('name', $name);
        $this->view()->assign('title', __('Pages list'));

        $this->view()->setTemplate('page-list');
    }

    /**
     * Add a page and its corresponding ACL resource
     */
    public function addAction()
    {
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form = new AddForm('page-edit', $data['module']);
            $form->setInputFilter(new AddFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                foreach (array_keys($values) as $key) {
                    if (!in_array($key, $this->pageColumns)) {
                        unset($values[$key]);
                    }
                }
                $values['custom'] = 1;
                $row = Pi::model('page')->createRow($values);
                $row->save();
                if ($row->id) {
                    $message = __('Page data saved successfully.');

                    // Add ACL resource
                    $pageParent = null;
                    $where = array(
                        'section'       => $values['section'],
                        'module'        => $values['module'],
                        'controller'    => '',
                        'action'        => '',
                    );
                    if (!empty($values['action'])) {
                        $where['controller'] = $values['controller'];
                        $pageParent = Pi::model('page')->select($where)
                            ->current();
                        if (!$pageParent) {
                            $where['controller'] = '';
                        }
                    }
                    if (!$pageParent) {
                        $pageParent = Pi::model('page')->select($where)
                            ->current();
                    }
                    $where = array(
                        'section'       => $values['section'],
                        'module'        => $values['module'],
                        'name'          => $pageParent->id,
                        'type'          => 'page',
                    );
                    $parent = Pi::model('acl_resource')->select($where)
                        ->current();
                    $resource = array(
                        'title'         => $values['title'],
                        'section'       => $values['section'],
                        'module'        => $values['module'],
                        'name'          => $row->id,
                        'type'          => 'page',
                    );
                    $resourceId =
                        Pi::model('acl_resource')->add($resource, $parent);

                    Pi::registry('page')->clear($row->module);
                    $this->redirect()->toRoute(
                        '',
                        array(
                            'action' => 'index',
                            'name' => $values['module']
                        )
                    );
                    $this->view()->setTemplate(false);
                } else {
                    $message = __('Page data not saved.');
                }
            } else {
                $message = __('Invalid data, please check and re-submit.');
            }
        } else {
            $form = new AddForm('page-edit', $this->params('name'));
            $form->setAttribute('action', $this->url('',
                                array('action' => 'addsave')));
            $message = '';
        }

        $this->view()->assign('form', $form);
        $this->view()->assign('title', __('Setup a page'));
        $this->view()->assign('message', $message);
        $this->view()->setTemplate('system:component/form-popup');
    }


    /**
     * AJAX method for adding a page and its corresponding ACL resource
     *
     * @return array Result pair of status and message
     */
    public function addsaveAction()
    {
        $status     = 1;
        $message    = '';
        $page       = array();

        $data = $this->request->getPost();
        $form = new AddForm('page-edit', $data['module']);
        $form->setInputFilter(new AddFilter);
        $form->setData($data);
        if ($form->isValid()) {
            $values = $form->getData();
            foreach (array_keys($values) as $key) {
                if (!in_array($key, $this->pageColumns)) {
                    unset($values[$key]);
                }
            }
            $values['custom'] = 1;
            $values['block'] = 1;
            $row = Pi::model('page')->createRow($values);
            $row->save();
            if ($row->id) {
                $message = __('Page data saved successfully.');

                // Add ACL resource
                $pageParent = null;
                $where = array(
                    'section'       => $values['section'],
                    'module'        => $values['module'],
                    'controller'    => '',
                    'action'        => '',
                );
                if (!empty($values['action'])) {
                    $where['controller'] = $values['controller'];
                    $pageParent = Pi::model('page')->select($where)->current();
                    if (!$pageParent) {
                        $where['controller'] = '';
                    }
                }
                if (!$pageParent) {
                    $pageParent = Pi::model('page')->select($where)->current();
                }
                $where = array(
                    'section'       => $values['section'],
                    'module'        => $values['module'],
                    'name'          => $pageParent->id,
                    'type'          => 'page',
                );
                $parent = Pi::model('acl_resource')->select($where)->current();
                $resource = array(
                    'title'         => $values['title'],
                    'section'       => $values['section'],
                    'module'        => $values['module'],
                    'name'          => $row->id,
                    'type'          => 'page',
                );
                $resourceId =
                    Pi::model('acl_resource')->add($resource, $parent);

                $id = $row->id;
                $page = array(
                    'id'        => $row->id,
                    'title'     => $row->title,
                    'edit'      => $this->url(
                        '',
                        array('action' => 'edit', 'id' => $row->id)
                    ),
                    'delete'    => $this->url(
                        '',
                        array('action' => 'delete', 'id' => $row->id)
                    ),
                    'dress'     => $this->url(
                        '',
                        array('action' => 'block', 'page' => $row->id)
                    ),
                );
                Pi::registry('page')->clear($row->module);

            } else {
                $message = __('Page data not saved.');
                $status = 1;
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
            'data'      => $page,
        );
    }

    /**
     * Edit a page
     */
    public function editAction()
    {
        $form = new EditForm('page-edit');
        if ($this->request->isPost()) {
            $data = $this->request->getPost();
            $form->setInputFilter(new EditFilter);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                $id = $values['id'];
                unset($values['id']);
                foreach (array_keys($values) as $key) {
                    if (!in_array($key, $this->pageColumns)) {
                        unset($values[$key]);
                    }
                }
                $row = Pi::model('page')->find($id);
                $row->assign($values);
                $row->save();
                Pi::registry('page')->clear($row->module);
                $message = __('Page data saved successfully.');
            } else {
                $message = __('Invalid data, please check and re-submit.');
            }
        } else {
            $id = $this->params('id');
            $values = Pi::model('page')->find($id)->toArray();
            $form->setData($values);
            $form->setAttribute(
                'action',
                $this->url('', array('action' => 'editsave'))
            );
            $message = '';
        }

        $this->view()->assign('form', $form);
        $this->view()->assign('title', __('Pages edit'));
        $this->view()->assign('message', $message);
        $this->view()->setTemplate('system:component/form-popup');
    }

    /**
     * AJAX for editing a page
     *
     * @return array Result pair of status and message
     */
    public function editsaveAction()
    {
        $status     = 1;
        $message    = '';
        $page       = array();

        $form = new EditForm('page-edit');
        $data = $this->request->getPost();
        $form->setInputFilter(new EditFilter);
        $form->setData($data);
        if ($form->isValid()) {
            $values = $form->getData();
            $id = $values['id'];
            unset($values['id']);
            foreach (array_keys($values) as $key) {
                if (!in_array($key, $this->pageColumns)) {
                    unset($values[$key]);
                }
            }
            $row = Pi::model('page')->find($id);
            $row->assign($values);
            $row->save();
            $message = __('Page data saved successfully.');

            $page = array(
                'id'    => $row->id,
                'title' => $row->title,
            );
            Pi::registry('page')->clear($row->module);

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
            'data'      => $page,
        );
    }

    /**
     * Delete a page and remove its corresponding ACL resource
     *
     * @return array Result pair of status and message
     */
    public function deleteAction()
    {
        $id = $this->params('id');
        $row = Pi::model('page')->find($id);
        // Only custom pages are allowed to delete
        if ($row && $row->custom) {
            // Remove ACL resource
            $modelResource = Pi::model('acl_resource');
            $rowResource = $modelResource->select(array(
                'section'   => $row->section,
                'module'    => $row->module,
                'name'      => $row->id,
            ))->current();
            $modelResource->remove($rowResource);

            // Remove ACL rules
            Pi::model('acl_rule')->delete(array(
                'resource'  => $rowResource->id,
                'section'   => $rowResource->section,
                'module'    => $rowResource->module,
            ));

            // Remove page-block links
            Pi::model('page_block')->delete(array('page' => $row->id));

            // Remove page
            $row->delete();
            Pi::registry('page')->clear($row->module);
            $result = array(
                'status'    => 0,
                'message'   => __('Page is not found.'),
            );
        } else {
            $result = array(
                'status'    => 1,
                'message'   => __('Page is deleted.'),
            );
        }

        return $result;
    }

    /**
     * Manipulate blocks of a page
     */
    public function blockAction()
    {
        // Module name
        $name = $this->params('name', '');
        // Page ID
        $page = $this->params('page', 0);

        $_this = clone $this;
        $fallback = function () use ($_this) {
            $_this->view()->setTemplate(false);
            $_this->redirect()->toRoute('', array('action' => 'index'));
        };

        // Get the page
        if (!$page) {
            $fallback();
            return;
        }

        // Get current page row
        $row = Pi::model('page')->find($page);
        if (!$row || !$row->block) {
            $fallback();
            return;
        }
        $pageName = $row->module;
        if ($row->controller) {
            $pageName .= '-' . $row->controller;
            if ($row->action) {
                $pageName .= '-' . $row->action;
            }
        }
        $pageData = array(
            'id'    => $row->id,
            'title' => $row->title,
            'name'  => $pageName,
        );

        // Fetch all blocks on the page
        $select = Pi::model('page_block')->select()->order('order')
            ->where(array('page' => $page));
        $rowset = Pi::model('page_block')->selectWith($select);

        // Get block IDs and block holder with block zone and order as
        $blockIds = array();
        $blockHolder = array();
        foreach ($rowset as $row) {
            $blockIds[] = $row->block;
            $blockHolder[$row->block] = array(
                'zone'      => $row->zone,
            );
        }

        // Build block list sorted by zone
        $blocks = array();
        if ($blockIds) {
            $rowset = Pi::model('block')->select(array('id' => $blockIds));
            foreach ($rowset as $row) {
                $blockHolder[$row->id]['block'] = array(
                    'id'            => $row->id,
                    'title'         => $row->title,
                    'description'   => $row->description,
                );
            }
        }

        foreach ($blockHolder as $id => $data) {
            if (empty($data['block'])) {
                continue;
            }
            $blocks[$data['zone']][] = $data['block'];
        }

        $model = Pi::model('block');
        $select = $model->select()->group('module')
            ->columns(array('count' => new Expression('count(*)'), 'module'));
        $rowset = $model->selectWith($select);
        $blockCounts = array();
        foreach ($rowset as $row) {
            $blockCounts[$row->module] = $row->count;
        }

        // Get module list
        $modules = array();
        $moduleSet = Pi::model('module')->select(array('active' => 1));
        foreach ($moduleSet as $row) {
            if (!empty($blockCounts[$row->name])) {
                $modules[] = array(
                    'name'  => $row->name,
                    'title' => $row->title
                               . ' (' . $blockCounts[$row->name] . ')',
                );
            }
        }

        // Get ready for view
        $this->view()->assign('page', $pageData);
        $this->view()->assign('blocks', $blocks);
        $this->view()->assign('currentTheme', Pi::config('theme'));
        $this->view()->assign('modules', $modules);
        $this->view()->assign('name', $name);
        $this->view()->assign('pageZone', $this->getZoneTemplate());
        $this->view()->assign('title',
                              sprintf(__('%s blocks'), $pageData['title']));
        $this->view()->setTemplate('page-block');
    }

    /**
     * AJAX methdod for getting blocks of a module
     *
     * @return array Result pair of status and message
     */
    public function blocklistAction()
    {
        // Module name
        $name = $this->params('name', '');

        $rowset = Pi::model('block')->select(array('module' => $name));
        $blocks = array();
        foreach ($rowset as $row) {
            $blocks[] = array(
                'id'            => $row->id,
                'title'         => $row->title,
                'description'   => $row->description,
            );
        }

        return array(
            'status'    => 1,
            'data'      => $blocks,
        );
    }

    /**
     * AJAX method of saving blocks of a page:
     *
     * ```
     *  array(
     *      'page'    => <page-id>,
     *      'blocks'  => array(
     *          'zone'    => <block-id[]>,
     *      ),
     *  );
     * ```
     *
     * @return array Result pair of status and message
     */
    public function saveAction()
    {
        $result = array(
            'status'    => 1,
            'message'   => '',
            'data'      => array(),
        );
        $page   = $this->params()->fromPost('page');
        $blocks = $this->params()->fromPost('blocks');

        $row = Pi::model('page')->find($page);
        if (!$row) {
            $result = array(
                'status'    => 0,
                'message'   => __('Page is not found.'),
            );

            return $result;
        }

        // Remove all existent block links
        Pi::model('page_block')->delete(array('page' => $page));
        // Add new block links
        foreach ($blocks as $zone => $list) {
            $order = 0;
            foreach ($list as $block) {
                Pi::model('page_block')->insert(array(
                    'page'  => $page,
                    'zone'  => $zone,
                    'block' => $block,
                    'order' => $order++,
                ));
            }
        }

        // Clear cache of the page module
        Pi::registry('block')->clear($row->module);
        $result = array(
            'status'    => 1,
            'message'   => __('Page block links are updated.'),
        );

        return $result;
    }

    /**
     * AJAX methdod for getting all active themes
     *
     * @return array
     */
    public function themelistAction()
    {
        $themeList = Pi::registry('themelist')->read('front');
        $themes = array();

        foreach ($themeList as $dirname => $theme) {
            $data = array(
                'name'  => $dirname,
                'title' => $theme['title'],
            );
            $themes[$dirname] = $data;
        }

        return $themes;
    }

    /**
     * AJAX methdod for getting action list of a controller
     *
     * @return array
     */
    public function actionlistAction()
    {
        $module = $this->params('name');
        $controller = $this->params('ctrl');
        $class = sprintf(
            'Module\\%s\Controller\Front\\%sController',
            ucfirst(Pi::service('module')->directory($module)),
            ucfirst($controller)
        );
        $methods = get_class_methods($class);
        $actions = array();
        foreach ($methods as $method) {
            if ('Action' == substr($method, -6)) {
                $actionName = substr($method, 0, -6);
                $actions[$actionName] = $actionName;
            }
        }

        return $actions;
    }

    /**
     * AJAX methdod for getting theme block zone template
     *
     * @return string
     */
    public function zonetemplateAction()
    {
        $theme = $this->params('theme', null);
        $template = $this->getZoneTemplate($theme);

        return $template;
    }

    /**
     * Get page zone template of a theme
     *
     * @param string $theme
     * @return string
     */
    protected function getZoneTemplate($theme = null)
    {
        $theme = $theme ?: Pi::config('theme');

        /**
         * @todo: Here the template is located via hardcoded trick,
         * is there any configurable way?
         */
        $templatePath = Pi::path('theme') . '/%s/template/page-zone.phtml';
        $path = sprintf($templatePath, $theme);

        if (!file_exists($path)) {
            $path = sprintf($templatePath, 'default');
        }
        $template = file_get_contents($path);

        // Convert zone ID from pi-zone-ID to pi-zone-ID-edit
        // for block manipulation
        $template = preg_replace(
            '|\{([\d]+)\}|',
            '<div id="pi-zone-$1-edit"></div>',
            $template
        );
        $template = str_replace(
            '{content}',
            '<div id="pi-content-fixed"></div>',
            $template
        );

        return $template;
    }
}
