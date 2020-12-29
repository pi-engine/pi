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
use Module\System\Form\BlockModuleForm;
use Pi;
use Laminas\Db\Sql\Predicate\Expression;

/**
 * Block manipulation controller
 *
 * Feature list:
 *
 *  1. List of blocks of a module
 *  2. Manage a block
 *  3. Clone a block
 *  4. Add a custom block
 *  5. Delete a block
 *  6. List of pages of a block
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class BlockController extends ComponentController
{
    /**
     * Get exceptions for permission check
     *
     * @return string
     */
    public function permissionException()
    {
        return 'page';
    }

    /**
     * Get module list
     *
     * @return array
     */
    protected function getModules()
    {
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
                    'title' => $row->title . ' (' . $blockCounts[$row->name] . ')',
                ];
            }
        }

        return $modules;
    }

    /**
     * List of blocks sorted by module
     *
     * @return void
     */
    public function indexAction()
    {
        // Module name, default as 'system'
        $name = $this->params('name', $this->moduleName('system'));

        if (!$this->permission($name, 'block')) {
            return;
        }

        // BLocks of the module
        $model  = Pi::model('block');
        $select = $model->select()->where(['module' => $name])
            ->order(['id ASC']);
        $rowset = $model->selectWith($select);
        $blocks = [];
        $list   = [];
        foreach ($rowset as $row) {
            $list[$row->id] = [
                'id'          => $row->id,
                'name'        => $row->name,
                'title'       => $row->title,
                'description' => $row->description,
                'module'      => $row->module,
                'root'        => $row->root,
                'cloned'      => $row->cloned,
                'type'        => $row->type,
                //'clonable'      => $row->render ? true : false,
                'clonable'    => true,
            ];
        }
        ksort($list);
        array_walk($list, function ($item) use (&$blocks, $name) {
            $item['previewUrl'] = $this->url(
                'default',
                ['module' => 'widget'],
                ['query' => ['block' => $item['id']]]
            );
            $item['editUrl']    = $this->url('', [
                'action' => 'edit',
                'id'     => $item['id'],
                'name'   => $name,
            ]);
            $item['deleteUrl']  = $this->url('', [
                'action' => 'delete',
                'id'     => $item['id'],
                'name'   => $name,
            ]);
            $item['cloneUrl']   = $this->url('', [
                'action' => 'clone',
                'id'     => $item['id'],
                'name'   => $name,
            ]);
            $blocks[]           = $item;
        });

        $this->view()->assign('blocks', $blocks);
        $this->view()->assign('name', $name);
        $this->view()->assign('title', _a('Block list'));
        $this->view()->setTemplate('block-list');
    }

    /**
     * Clone a block and default ACL rules
     *
     * @return void
     */
    public function cloneAction()
    {
        if ($this->request->isPost()) {
            $data    = $this->request->getPost();
            $base    = $data['id'];
            $baseRow = Pi::model('block')->find($base);
            if (!$baseRow) {
                $message = _a('Base block is not found.');
                $this->jump(
                    ['action' => 'index'],
                    $message,
                    'error'
                );
                return;
            }
            $root    = $baseRow->root;
            $rootRow = Pi::model('block_root')->find($root);
            /*
            if (!$rootRow->render) {
                $message = _a('The block is not allowed to clone.');
                $this->jump(
                    array('action' => 'index', 'name' => $rootRow->module),
                    $message,
                    'error'
                );
                return;
            }
            */
            $form = new BlockModuleForm('block-edit', $rootRow, true);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $baseRow->toArray();
                $values = array_replace($values, $form->getData());
                //$values = $form->getData();
                $values['cloned'] = 1;
                //$values['root']     = $rootRow->id;
                //$values['module']   = $rootRow->module;
                //$values['render']   = $rootRow->render;
                //$values['content']  = $baseRow->content;
                unset($values['id']);

                $result = Pi::api('block', 'system')->add($values);
                if (!empty($result['status'])) {
                    $message = _a('Block data saved successfully.');
                    $this->jump(
                        ['action' => 'index', 'name' => $rootRow->module],
                        $message
                    );
                    return;
                } else {
                    $message = _a('Block data not saved.');
                }
            } else {
                $message = _a('Invalid data, please check and re-submit.');
            }
        } else {
            $base    = $this->params('id');
            $baseRow = Pi::model('block')->find($base);
            if (!$baseRow) {
                $message = _a('Base block is not found.');
                $this->jump(
                    ['action' => 'index'],
                    $message,
                    'error'
                );
                return;
            }
            $root    = $baseRow->root;
            $rootRow = Pi::model('block_root')->find($root);
            /*
            if (!$rootRow->render) {
                $message = _a('The block is not allowed to clone.');
                $this->jump(
                    array('action' => 'index', 'name' => $rootRow->module),
                    $message,
                    'error'
                );
                return;
            }
            */
            $form = new BlockModuleForm('block-edit', $rootRow, true);
            // Fetch block data
            $data = $baseRow->toArray();
            // Set cloned title
            $data['title'] = sprintf(_a('%s clone'), $data['title']);
            // Set cloned name
            $data['name'] = sprintf('%s-clone', $data['name']);
            // Set root id
            //$data['root'] = $root;
            // Remove root id
            //unset($data['id']);
            // Fetch config values
            if (!empty($data['config'])) {
                foreach ($data['config'] as $key => $config) {
                    if (isset($config['value'])) {
                        $data['config'][$key] = $config['value'];
                    } else {
                        unset($data['config'][$key]);
                    }
                }
            }

            $form->setData($data);
            $form->setAttribute(
                'action',
                $this->url('', ['action' => 'clone'])
            );
            $message = '';
        }

        $title = sprintf(_a('Block clone from: %s'), $baseRow->title);

        $this->view()->assign('title', $title);
        $this->view()->assign('name', $rootRow->module);
        $this->view()->assign('form', $form);
        $this->view()->assign('message', $message);
        $this->view()->setTemplate('system:component/forms');
    }

    /**
     * Edit a block
     *
     * @return void
     */
    public function editAction()
    {
        if ($this->request->isPost()) {
            $data     = $this->request->getPost();
            $id       = $data['id'];
            $blockRow = Pi::model('block')->find($id);
            $rootRow  = Pi::model('block_root')->find($blockRow->root);

            $form = new BlockModuleForm('block-edit', $rootRow, $blockRow->cloned);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                // Only cloned blocks are allowed to change template
                if (!$blockRow->cloned && isset($values['template'])) {
                    unset($values['template']);
                }
                // Start add by voltan
                if (isset($rootRow->config) && !empty($rootRow->config)) {
                    $values['config'] = [];
                    foreach ($rootRow->config as $name => $field) {
                        if (isset($values[$name])) {
                            $values['config'][$name] = $values[$name];
                            unset($values[$name]);
                        }
                    }
                }
                // End add by voltan
                $result  = Pi::api('block', 'system')->updateBlock($blockRow, $values);
                $message = _a('Block data saved successfully.');
                $this->jump(
                    ['action' => 'index', 'name' => $blockRow->module],
                    $message
                );
                return;
            } else {
                $message = _a('Invalid data, please check and re-submit.');
            }
        } else {
            $id       = $this->params('id');
            $blockRow = Pi::model('block')->find($id);
            if (!$blockRow) {
                $message = _a('Block is not found.');
                $this->jump(['action' => 'index'], $message, 'error');
                return;
            }
            $rootRow = Pi::model('block_root')->find($blockRow->root);
            $form    = new BlockModuleForm('block-edit', $rootRow, $blockRow->cloned);
            $values  = $blockRow->toArray();
            // Start add by voltan
            if (isset($values['config']) && !empty($values['config'])) {
                foreach ($values['config'] as $name => $value) {
                    $values[$name] = $value;
                }
            }
            // End add by voltan
            $form->setData($values);
            $form->setAttribute(
                'action',
                $this->url('', ['action' => 'edit'])
            );
            $message = '';
        }

        $title = sprintf(_a('Block edit: %s'), $blockRow->title);

        $this->view()->assign('title', $title);
        //$this->view()->assign('modules', $this->getModules());
        $this->view()->assign('name', $blockRow->module);
        $this->view()->assign('form', $form);
        $this->view()->assign('message', $message);
        $this->view()->setTemplate('system:component/forms');
    }

    /**
     * Delete a block and remove its page-block links
     *
     * @return int
     */
    public function deleteAction()
    {
        $id     = $this->params('id');
        $result = Pi::api('block', 'system')->delete($id, false);

        $message = _a('Block is deleted.');
        $this->jump(['action' => 'index'], $message);
    }

    /**
     * List of pages that have the block
     *
     * @return array
     */
    public function pageAction()
    {
        $pages   = [];
        $id      = $this->params('id');
        $rowset  = Pi::model('page_block')->select(['block' => $id]);
        $pageIds = [];
        foreach ($rowset as $row) {
            $pageIds[] = $row->page;
        }
        if ($pageIds) {
            $modules  = Pi::registry('module')->read();
            $select   = Pi::model('page')->select()
                ->where(['id' => $pageIds])
                ->order(['module ASC', 'controller ASC', 'action ASC']);
            $rowset   = Pi::model('page')->selectWith($select);
            $pageList = [];
            foreach ($rowset as $row) {
                $pageList[$row->module][] = [
                    'title' => $row->title,
                    'url'   => $this->url('', [
                        'controller' => 'page',
                        'action'     => 'block',
                        'page'       => $row->id,
                    ]),
                ];
            }

            foreach ($modules as $name => $data) {
                if (!isset($pageList[$name])) {
                    continue;
                }
                $pages[$name] = [
                    'title' => $data['title'],
                    'pages' => $pageList[$name],
                ];
            }
        }

        return $pages;
    }
}
