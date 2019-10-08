<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Controller\Admin;

use Module\System\Controller\ComponentController;
use Module\System\Form\PageAddFilter as AddFilter;
use Module\System\Form\PageAddForm as AddForm;
use Module\System\Form\PageEditFilter as EditFilter;
use Module\System\Form\PageEditForm as EditForm;
use Pi;
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
class PageController extends ComponentController
{
    /**
     * Columns for page model
     * @var string[]
     */
    protected $pageColumns
        = [
            'id', 'section', 'module', 'controller', 'action', 'block', 'custom',
            //'cache_type', 'cache_ttl', 'cache_level',
            'title',
        ];

    /**
     * List of pages sorted by module and section
     */
    public function indexAction()
    {
        // Module name, default as 'system'
        $name = $this->params('name', $this->moduleName('system'));

        if (!$this->permission($name, 'page')) {
            return;
        }

        // Pages of the module
        $select   = Pi::model('page')->select()
            ->where(['module' => $name, 'section' => 'front'])
            ->order(['custom', 'controller', 'action', 'id']);
        $rowset   = Pi::model('page')->selectWith($select);
        $sections = [
            'front' => [
                'title' => _a('Front'),
                'pages' => [],
            ],
            /*
            'admin' => array(
                'title' => _a('Admin'),
                'pages' => array(),
            ),
            'feed'  => array(
                'title' => _a('Feed'),
                'pages' => array(),
            ),
            */
        ];

        // Organized pages by section
        $pageModule = [];
        $pageHome   = [];
        foreach ($rowset as $row) {
            //$sections[$row->section]['pages'][] = $row->toArray();
            if (!$row->controller) {
                $pageModule = [
                    'id'      => $row->id,
                    'title'   => _a('Module wide'),
                    'key'     => $row->module,
                    'section' => $row->section,
                    'custom'  => $row->custom,
                    'block'   => 1, //$row->block,
                ];
                continue;
            } elseif ('index' == $row->controller && 'index' == $row->action) {
                $pageHome = [
                    'id'      => $row->id,
                    'title'   => _a('Module home'),
                    'key'     => $row->module . 'index-index',
                    'section' => $row->section,
                    'custom'  => $row->custom,
                    'block'   => $row->block,
                ];
                continue;
            }

            $key                                = $row->module . '-' . $row->controller;
            $key                                .= $row->action ? '-' . $row->action : '';
            $sections[$row->section]['pages'][] = [
                'id'      => $row->id,
                'title'   => $row->title ?: $key,
                'key'     => $key,
                'section' => $row->section,
                'custom'  => $row->custom,
                'block'   => $row->block,
                //'link'      => '',
            ];
        }
        if ($pageHome) {
            array_unshift($sections['front']['pages'], $pageHome);
        }
        if ($pageModule) {
            array_unshift($sections['front']['pages'], $pageModule);
        }


        $this->view()->assign('pagesBySection', $sections);
        $this->view()->assign('name', $name);
        $this->view()->assign('title', _a('Pages list'));

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
                $row              = Pi::model('page')->createRow($values);
                $row->save();
                if ($row->id) {
                    $message = _a('Page data saved successfully.');

                    Pi::registry('page')->clear($row->module);
                    $this->redirect()->toRoute(
                        '',
                        [
                            'action' => 'index',
                            'name'   => $values['module'],
                        ]
                    );
                    $this->view()->setTemplate(false);
                } else {
                    $message = _a('Page data not saved.');
                }
            } else {
                $message = _a('Invalid data, please check and re-submit.');
            }
        } else {
            $form = new AddForm('page-edit', $this->params('name'));
            $form->setAttribute(
                'action',
                $this->url('', ['action' => 'addsave'])
            );
            $message = '';
        }

        $this->view()->assign('form', $form);
        $this->view()->assign('title', _a('Setup a page'));
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
        $status = 1;
        //$message    = '';
        $page = [];

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
            $values['block']  = 1;
            $row              = Pi::model('page')->createRow($values);
            $row->save();
            if ($row->id) {
                $message = _a('Page data saved successfully.');

                $id   = $row->id;
                $page = [
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
                    'dress'  => $this->url(
                        '',
                        ['action' => 'block', 'page' => $row->id]
                    ),
                ];
                Pi::registry('page')->clear($row->module);

            } else {
                $message = _a('Page data not saved.');
                $status  = 1;
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
            'data'    => $page,
        ];
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
                $id     = $values['id'];
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
                $message = _a('Page data saved successfully.');
            } else {
                $message = _a('Invalid data, please check and re-submit.');
            }
        } else {
            $id     = $this->params('id');
            $values = Pi::model('page')->find($id)->toArray();
            $form->setData($values);
            $form->setAttribute(
                'action',
                $this->url('', ['action' => 'editsave'])
            );
            $message = '';
        }

        $this->view()->assign('form', $form);
        $this->view()->assign('title', _a('Pages edit'));
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
        $status = 1;
        //$message    = '';
        $page = [];

        $form = new EditForm('page-edit');
        $data = $this->request->getPost();
        $form->setInputFilter(new EditFilter);
        $form->setData($data);
        if ($form->isValid()) {
            $values = $form->getData();
            $id     = $values['id'];
            unset($values['id']);
            foreach (array_keys($values) as $key) {
                if (!in_array($key, $this->pageColumns)) {
                    unset($values[$key]);
                }
            }
            $row = Pi::model('page')->find($id);
            $row->assign($values);
            $row->save();
            $message = _a('Page data saved successfully.');

            $page = [
                'id'    => $row->id,
                'title' => $row->title,
            ];
            Pi::registry('page')->clear($row->module);

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
            'data'    => $page,
        ];
    }

    /**
     * Delete a page and remove its corresponding ACL resource
     *
     * @return array Result pair of status and message
     */
    public function deleteAction()
    {
        $id  = $this->params('id');
        $row = Pi::model('page')->find($id);
        // Only custom pages are allowed to delete
        if ($row && $row->custom) {

            // Remove page-block links
            Pi::model('page_block')->delete(['page' => $row->id]);

            // Remove page
            $row->delete();
            Pi::registry('page')->clear($row->module);
            $result = [
                'status'  => 1,
                'message' => _a('Page is deleted.'),
            ];
        } else {
            $result = [
                'status'  => 0,
                'message' => _a('Page is not found.'),
            ];
        }

        return $result;
    }

    /**
     * Manipulate blocks of a page
     */
    public function blockAction()
    {
        // Module name
        $name = $this->params('name', $this->moduleName('system'));
        // Page ID
        $page = $this->params('page', 0);

        $fallback = function () {
            $this->view()->setTemplate(false);
            $this->redirect()->toRoute('', ['action' => 'index']);
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
            $title = $row->title;
        } else {
            $title = _a('Module wide');
        }
        $pageData = [
            'id'    => $row->id,
            'title' => $title,
            'name'  => $pageName,
        ];

        // Fetch all blocks on the page
        $select = Pi::model('page_block')->select()->order('order')
            ->where(['page' => $page]);
        $rowset = Pi::model('page_block')->selectWith($select);

        // Get block IDs and block holder with block zone and order as
        $blockIds    = [];
        $blockHolder = [];
        foreach ($rowset as $row) {
            $blockIds[]               = $row->block;
            $blockHolder[$row->block] = [
                'zone' => $row->zone,
            ];
        }

        // Build block list sorted by zone
        $blocks = [];
        if ($blockIds) {
            $rowset = Pi::model('block')->select(['id' => $blockIds]);
            foreach ($rowset as $row) {
                $blockHolder[$row->id]['block'] = [
                    'id'          => $row->id,
                    'title'       => $row->title,
                    'description' => $row->description,
                ];
            }
        }

        foreach ($blockHolder as $id => $data) {
            if (empty($data['block'])) {
                continue;
            }
            $blocks[$data['zone']][] = $data['block'];
        }

        $model       = Pi::model('block');
        $select      = $model->select()->group('module')
            ->columns(['count' => new Expression('count(*)'), 'module']);
        $rowset      = $model->selectWith($select);
        $blockCounts = [];
        foreach ($rowset as $row) {
            $blockCounts[$row->module] = $row->count;
        }

        // Get module list
        $modules   = [];
        $moduleSet = Pi::model('module')->select(['active' => 1]);
        foreach ($moduleSet as $row) {
            if (!empty($blockCounts[$row->name])) {
                $modules[] = [
                    'name'  => $row->name,
                    'title' => $row->title
                        . ' (' . $blockCounts[$row->name] . ')',
                ];
            }
        }

        // Get ready for view
        $this->view()->assign('page', $pageData);
        $this->view()->assign('blocks', $blocks);
        $this->view()->assign('currentTheme', Pi::config('theme'));
        $this->view()->assign('modules', $modules);
        $this->view()->assign('name', $name);
        $this->view()->assign('pageZone', $this->getZoneTemplate());
        $this->view()->assign('title', sprintf(
            _a('%s blocks'),
            $pageData['title']
        ));
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
        $name = $this->params('name', $this->moduleName('system'));

        $rowset = Pi::model('block')->select(['module' => $name]);
        $blocks = [];
        foreach ($rowset as $row) {
            $blocks[] = [
                'id'          => $row->id,
                'title'       => $row->title,
                'description' => $row->description,
            ];
        }

        return [
            'status' => 1,
            'data'   => $blocks,
        ];
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
        $result = [
            'status'  => 1,
            'message' => '',
            'data'    => [],
        ];
        $page   = $this->params()->fromPost('page');
        $blocks = $this->params()->fromPost('blocks');

        $row = Pi::model('page')->find($page);
        if (!$row) {
            $result = [
                'status'  => 0,
                'message' => _a('Page is not found.'),
            ];

            return $result;
        }

        // Remove all existent block links
        Pi::model('page_block')->delete(['page' => $page]);
        // Add new block links
        foreach ($blocks as $zone => $list) {
            $order = 0;
            foreach ($list as $block) {
                Pi::model('page_block')->insert([
                    'page'  => $page,
                    'zone'  => $zone,
                    'block' => $block,
                    'order' => $order++,
                ]);
            }
        }

        // Clear cache of the module
        Pi::registry('block')->clear($row->module);
        Pi::service('cache')->flush('module', $row->module);
        $result = [
            'status'  => 1,
            'message' => _a('Page block links are updated.'),
        ];

        return $result;
    }

    /**
     * AJAX method for fetching all active themes
     *
     * @return array
     */
    public function themelistAction()
    {
        $themeList = Pi::registry('themelist')->read('front');
        $themes    = [];

        foreach ($themeList as $dirname => $theme) {
            $data             = [
                'name'  => $dirname,
                'title' => $theme['title'],
            ];
            $themes[$dirname] = $data;
        }

        return $themes;
    }

    /**
     * AJAX method for getting action list of a controller
     *
     * @return array
     */
    public function actionlistAction()
    {
        $name       = $this->params('name', $this->moduleName('system'));
        $controller = $this->params('ctrl');
        $class      = sprintf(
            'Module\\%s\Controller\Front\\%sController',
            ucfirst(Pi::service('module')->directory($name)),
            ucfirst($controller)
        );
        $methods    = get_class_methods($class);
        $actions    = [];
        foreach ($methods as $method) {
            if ('Action' == substr($method, -6)) {
                $actionName           = substr($method, 0, -6);
                $actions[$actionName] = $actionName;
            }
        }

        return $actions;
    }

    /**
     * AJAX method for getting theme block zone template
     *
     * @return string
     */
    public function zonetemplateAction()
    {
        $theme    = $this->params('theme', null);
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
        $path         = sprintf($templatePath, $theme);

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

    public function homepageAction()
    {
        return $this->redirect()->toRoute('', [
            'controller' => 'page',
            'action'     => 'block',
            'page'       => '3',
            'name'       => 'system',
        ]);
    }
}
