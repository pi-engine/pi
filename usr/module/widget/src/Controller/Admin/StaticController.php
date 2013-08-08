<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
            'html'      => __('HTML'),
            'text'      => __('Text'),
            'markdown'  => __('Markdown'),
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

        $this->view()->assign('types', $contentTypes);
        $this->view()->assign('widgets', $widgets);
        $this->view()->assign('title', __('Static widgets'));
        $this->view()->setTemplate('list-static');
    }

    /**
     * Add a block and default ACL rules
     */
    public function addAction()
    {
        if ($this->request->isPost()) {
            $this->type = $this->request->getPost('type');
        } else {
            $this->type = $this->params('type', 'html');
        }
        parent::addAction();
        $this->view()->setTemplate('widget-static');
        $this->view()->assign('type', $this->type);
        $this->view()->assign('types', $this->contentTypes());
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
                $message = __('Block data saved successfully.');
                $this->jump(array('action' => 'index', 'name' => ''),
                            $message);

                return;
            } elseif ($status < 0) {
                $message = __('Block data not saved.');
            } else {
                $message = __('Invalid data, please check and re-submit.');
            }
        } else {
            $blockRow = Pi::model('block_root')->find($widget->block);

            $values = $blockRow->toArray();
            $values['id'] = $id;
            $values['content'] = $widget->meta;
            $form->setData($values);
            $message = '';
        }

        $this->view()->assign('title', __('Block edit'));
        $this->view()->assign('form', $form);
        $this->view()->assign('message', $message);

        $this->view()->setTemplate('widget-static');
        $this->view()->assign('type', $this->type);
        $this->view()->assign('types', $this->contentTypes());
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
