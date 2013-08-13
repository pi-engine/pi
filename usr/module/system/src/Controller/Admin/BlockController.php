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
use Module\System\Form\BlockModuleForm as ModuleForm;
use Zend\Db\Sql\Predicate\Expression;

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
class BlockController extends ActionController
{
    /**
     * Get module list
     *
     * @return array
     */
    protected function getModules()
    {
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
        $name = $this->params('name', 'system');

        // BLocks of the module
        $model = Pi::model('block');
        $select = $model->select()->where(array('module' => $name))
            ->order(array('id ASC'));
        $rowset = $model->selectWith($select);
        $blocks = array();
        foreach ($rowset as $row) {
            $blocks[$row->id] = array(
                'id'            => $row->id,
                'name'          => $row->name,
                'title'         => $row->title,
                'description'   => $row->description,
                'module'        => $row->module,
                'root'          => $row->root,
                'cloned'        => $row->cloned,
                'type'          => $row->type,
                'clonable'      => $row->render ? true : false,
            );
        }
        ksort($blocks);

        //$this->view()->assign('modules', $this->getModules());
        $this->view()->assign('blocks', array_values($blocks));
        $this->view()->assign('name', $name);
        $this->view()->assign('title', __('Block list'));
        //$this->view()->assign('message', $message);
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
            $data = $this->request->getPost();
            $rootRow = Pi::model('block_root')->find($data['root']);
            if (!$rootRow->render) {
                $message = __('The block is not allowed to clone.');
                $this->jump(
                    array('action' => 'index', 'name' => $rootRow->module),
                    $message
                );
                return;
            }
            $form = new ModuleForm('block-edit', $rootRow);

            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                $values['cloned']   = 1;
                $values['root']     = $rootRow->id;
                $values['module']   = $rootRow->module;
                $values['template'] = $rootRow->template;
                $values['render']   = $rootRow->render;
                unset($values['id']);

                $result = Pi::api('system', 'block')->add($values);
                extract($result);
                if ($status) {
                    $message = __('Block data saved successfully.');
                    $this->jump(
                        array('action' => 'index', 'name' => $rootRow->module),
                        $message
                    );
                    return;
                } else {
                    $message = __('Block data not saved.');
                }
            } else {
                $message = __('Invalid data, please check and re-submit.');
            }
        } else {
            $root = $this->params('root');
            $rootRow = Pi::model('block_root')->find($root);
            if (!$rootRow->render) {
                $message = __('The block is not allowed to clone.');
                $this->jump(
                    array('action' => 'index', 'name' => $rootRow->module),
                    $message
                );
                return;
            }
            $form = new ModuleForm('block-edit', $rootRow);
            // Fetch block root data
            $data = $rootRow->toArray();
            // Set root id
            $data['root'] = $root;
            // Remove root id
            unset($data['id']);
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
                $this->url('', array('action' => 'clone'))
            );
            $message = '';
        }

        $title = sprintf(__('Block clone from: %s'), $rootRow->title);

        $this->view()->assign('title', $title);
        //$this->view()->assign('modules', $this->getModules());
        $this->view()->assign('name', $rootRow->module);
        $this->view()->assign('form', $form);
        $this->view()->assign('message', $message);
        $this->view()->setTemplate('block-edit');
    }

    /**
     * Edit a block
     *
     * @return void
     */
    public function editAction()
    {
        if ($this->request->isPost()) {
            $data = $this->request->getPost();

            $id = $data['id'];
            $blockRow = Pi::model('block')->find($id);
            /*
            if ($blockRow->module) {
                $rootRow = Pi::model('block_root')->find($blockRow->root);
                $form = new ModuleForm('block-edit', $rootRow);
            } else {
                $form = new CustomForm('block-custom', $blockRow->type);
                $form->setInputFilter(new CustomFilter);
            }
            */
            $rootRow = Pi::model('block_root')->find($blockRow->root);
            $form = new ModuleForm('block-edit', $rootRow);
            $form->setData($data);
            if ($form->isValid()) {
                $values = $form->getData();
                $result = Pi::api('system', 'block')->edit($blockRow, $values);
                $message = __('Block data saved successfully.');
                $this->jump(
                    array('action' => 'index', 'name' => $blockRow->module),
                    $message
                );
                return;
            } else {
                $message = __('Invalid data, please check and re-submit.');
            }
        } else {
            $id = $this->params('id');
            $blockRow = Pi::model('block')->find($id);
            if (!$blockRow) {
                $message = __('Block is not found.');
                $this->jump(array('action' => 'index'), $message);
                return;
            }
            $rootRow = Pi::model('block_root')->find($blockRow->root);
            $form = new ModuleForm('block-edit', $rootRow);
            $values = $blockRow->toArray();
            $form->setData($values);
            $form->setAttribute(
                'action',
                $this->url('', array('action' => 'edit'))
            );
            $message = '';
        }

        $title = sprintf(__('Block edit: %s'), $blockRow->title);

        $this->view()->assign('title', $title);
        //$this->view()->assign('modules', $this->getModules());
        $this->view()->assign('name', $blockRow->module);
        $this->view()->assign('form', $form);
        $this->view()->assign('message', $message);
        $this->view()->setTemplate('block-edit');
    }

    /**
     * AJAX for deleting a block and remove its page-block links
     * and corresponding ACL rules
     *
     * @return int
     */
    public function deleteAction()
    {
        $id = $this->params('id');
        $result = Pi::api('system', 'block')->delete($id, false);
        extract($result);
        return $status;
    }

    /**
     * List of pages that have the block
     *
     * @return array
     */
    public function pageAction()
    {
        $pages = array();
        $id = $this->params('id');
        $rowset = Pi::model('page_block')->select(array('block' => $id));
        $pageIds = array();
        foreach ($rowset as $row) {
            $pageIds[] = $row->page;
        }
        if ($pageIds) {

            $modules = Pi::registry('module')->read();
            /*
            $modules[''] = array(
                'title' => __('Custom blocks'),
            );
            */
            $select = Pi::model('page')->select()
                ->where(array('id' => $pageIds))
                ->order(array('module ASC', 'controller ASC', 'action ASC'));
            $rowset = Pi::model('page')->selectWith($select);
            $pageList = array();
            foreach ($rowset as $row) {
                $pageList[$row->module][] = array(
                    'title' => $row->title,
                    'url'   => $this->url('', array(
                        'controller'    => 'page',
                        'action'        => 'block',
                        'page'          => $row->id
                    )),
                );
            }

            foreach ($modules as $name => $data) {
                if (!isset($pageList[$name])) {
                    continue;
                }
                $pages[$name] = array(
                    'title'     => $data['title'],
                    'pages'     => $pageList[$name],
                );
            }
        }

        return $pages;
    }
}
