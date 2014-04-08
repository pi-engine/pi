<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Widget\Controller\Admin;

use Pi;
use Module\Widget\Form\BlockStaticForm as BlockForm;

/**
 * For static block
 */
class StaticController extends WidgetController
{
    protected $type = 'html';

    protected function getForm()
    {
        return new BlockForm('block', $this->type);
    }

    protected function contentTypes()
    {
        $contentTypes = array(
            'html'      => _a('HTML'),
            'text'      => _a('Text'),
            'markdown'  => _a('Markdown'),
        );

        return $contentTypes;
    }

    /**
     * List of carousel widgets
     */
    public function indexAction()
    {
        $contentTypes = $this->contentTypes();
        $model = $this->getModel('widget');
        $rowset = $model->select(array('type' => array_keys($contentTypes)));
        $widgets = array();
        foreach ($rowset as $row) {
            $widgets[$row->block] = $row->toArray();
        }
        if ($widgets) {
            $blocks = Pi::model('block_root')
                ->select(array('id' => array_keys($widgets)))->toArray();
            foreach ($blocks as $block) {
                $widgets[$block['id']]['block'] = $block;
            }
        }

        $data = array(
            'widgets'  => array_values($widgets)
        );

        $this->view()->assign('data', $data);
        $this->view()->setTemplate('ng');
    }

    /**
     * Add a block
     */
    public function addAction()
    {
        if ($this->request->isPost()) {
            $this->type = $this->request->getPost('type');
        } else {
            $this->type = $this->params('type', 'html');
        }
        parent::addAction();
        $this->view()->setTemplate('system:component/form');
    }

    /**
     * Edit a block
     */
    public function editAction()
    {
        if ($this->request->isPost()) {
            $id = $this->request->getPost('id');
        } else {
            $id = $this->params('id');
        }
        $widget = $this->getModel('widget')->find($id);
        $this->type = $widget->type;

        $form = $this->getForm();
        if ($this->request->isPost()) {
            $status = $this->processPost($form);
            if ($status > 0) {
                $message = _a('Block data saved successfully.');
                $this->jump(array('action' => 'index', 'name' => ''), $message);

                return;
            } elseif ($status < 0) {
                $message = _a('Block data not saved.');
            } else {
                $message = _a('Invalid data, please check and re-submit.');
            }
        } else {
            $blockRow = Pi::model('block_root')->find($widget->block);

            $values = $blockRow->toArray();
            $values['id'] = $id;
            $values['content'] = $widget->meta;
            $form->setData($values);
            $message = '';
        }

        $this->view()->assign('form', $form);
        $this->view()->assign('message', $message);
        $this->view()->setTemplate('system:component/form');
    }

    /**
     * Delete a block
     */
    public function deleteAction()
    {
        $result = $this->deleteBlock();
        $this->jump(array('action' => 'index'), $result['message']);
    }
}
