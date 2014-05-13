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
use Pi\Mvc\Controller\ActionController;
use Module\Widget\Form\AbstractBaseForm;

/**
 * For static block
 */
abstract class WidgetController extends ActionController
{
    /** @var  string Content type */
    protected $type = '';

    /** @var  string Template for `add` and `edit` action */
    protected $editTemplate = '';

    /** @var  string Form class */
    protected $formClass = '';

    /**
     * List of widgets
     */
    public function indexAction()
    {
        $data = array(
            'widgets' => array_values($this->widgetList())
        );
        $this->view()->assign('data', $data);
        $this->view()->setTemplate('ng');
    }

    /**
     * Load content form
     *
     * @return AbstractBaseForm|null
     */
    protected function getForm()
    {
        $form = null;
        if ($this->formClass) {
            $formClass = 'Module\Widget\Form\\' . $this->formClass;
            $form = new $formClass('block', $this->type);
        }

        return$form ;
    }

    /**
     * Add a block
     *
     * @param array $block
     *
     * @return int
     */
    protected function addBlock(array $block)
    {
        $status = 0;
        $module = $this->getModule();
        $block['module'] = $module;

        if (!isset($block['content'])) {
            $block['content'] = '';
        }
        $block['meta'] = $block['content'];
        $block['content'] = $this->canonizeContent($block['content']);
        $block['type'] = $this->type;
        $id = Pi::api('block', $module)->add($block);
        if ($id) {
            $status = 1;
            Pi::registry('block')->clear($module);
        }

        return $status;
    }

    /**
     * Update a block
     *
     * @param Row $widgetRow
     * @param array $block
     *
     * @return int
     */
    protected function updateBlock($widgetRow, array $block)
    {
        $block['meta'] = $block['content'];
        $block['content'] = $this->canonizeContent($block['content']);
        if (isset($block['type'])) {
            unset($block['type']);
        }

        $block['content'] = json_encode($block['content']);
        $result = Pi::api('block', 'system')->update(
            $widgetRow->block,
            $block
        );
        $status = $result['status'];
        if ($status) {
            $widgetRow->name = $block['name'];
            $widgetRow->meta = $block['meta'];
            $widgetRow->time = time();
            $widgetRow->save();
        }

        return $status;
    }

    /**
     * Delete a block
     *
     * @return array
     */
    protected function deleteBlock()
    {
        $id = $this->params('id');
        if ($id) {
            $row = $this->getModel('widget')->find($id);
        } else {
            $name = $this->params('name');
            $row = $this->getModel('widget')->find($name, 'name');
        }
        if (empty($row)) {
            $status = 0;
            $message = _a('The widget does not exist.');
        } else {
            $result = Pi::api('block', 'system')->delete($row->block, true);
            if (!empty($result['status'])) {
                $row->delete();
                Pi::registry('block')->clear($this->getModule());
                $status = 1;
                $message = sprintf(
                    _a('The widget "%s" is removed.'),
                    $row->name
                );
            } else {
                $status = 0;
                $message = sprintf(
                    _a('The widget "%s" is not removed.'),
                    $row->name
                );
            }
        }

        return array(
            'status'    => $status,
            'message'   => $message,
        );
    }

    /**
     * Process POST data for a form
     *
     * @param AbstractBaseForm $form
     *
     * @return int
     */
    protected function processPost(AbstractBaseForm $form)
    {
        $status = 0;
        $data = $this->getRequest()->getPost();

        $form->setData($data);
        if ($form->isValid()) {
            $values = $form->getData();
            $values = $this->canonizePost($values);
            if (isset($values['id'])) {
                $id = $values['id'];
                if (!$id) {
                    unset($values['id']);
                }
            } else {
                $id = null;
            }

            if ($id) {
                $row = $this->getModel('widget')->find($id);
                $status = $this->updateBlock($row, $values);
            } else {
                $values['type'] = !empty($values['type'])
                    ? $values['type']
                    : $this->type;
                $status = $this->addBlock($values);
            }

            if (!$status) {
                $status = -1;
            }
        }

        return $status;
    }

    /**
     * Get widget list
     */
    protected function widgetList()
    {
        $model = $this->getModel('widget');
        $rowset = $model->select(array('type' => $this->type));
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

        return $widgets;
    }

    /**
     * Add a block and default ACL rules
     */
    public function addAction()
    {
        $form = $this->getForm();
        if ($this->request->isPost()) {
            $status = $this->processPost($form);
            if ($status > 0) {
                $message = _a('Block data saved successfully.');
                $this->jump(
                    array('action' => 'index', 'name' => ''),
                    $message
                );

                return;
            } elseif ($status < 0) {
                $message = _a('Block data not saved.');
            } else {
                $formMessage = $form->getMessage();
                $message = $formMessage
                    ?: _a('Invalid data, please check and re-submit.');
            }
            $content = $this->request->getPost('content');
        } else {
            $content = '';
            $message = '';
        }

        $this->view()->assign('form', $form);
        $this->view()->assign('content', $content);
        $this->view()->assign('message', $message);
        $this->view()->assign('title', _a('Add a block'));
        if ($this->editTemplate) {
            $this->view()->setTemplate($this->editTemplate);
        }
    }

    /**
     * Edit a block
     */
    public function editAction()
    {
        $form = $this->getForm();
        if ($this->request->isPost()) {
            $status = $this->processPost($form);
            if ($status > 0) {
                $message = _a('Block data saved successfully.');
                $this->jump(
                    array('action' => 'index', 'name' => ''),
                    $message
                );

                return;
            } elseif ($status < 0) {
                $message = _a('Block data not saved.');
            } else {
                $message = _a('Invalid data, please check and re-submit.');
            }
            $content = $this->request->getPost('content');
        } else {
            $id = $this->params('id');
            $row = $this->getModel('widget')->find($id);
            $content = $row->meta;

            $blockRow = Pi::model('block_root')->find($row->block);
            $values = $this->prepareFormValues($blockRow);
            $values['id'] = $id;
            $form->setData($values);
            $message = '';
        }

        $this->view()->assign('title', _a('Block edit'));
        $this->view()->assign('content', $content);
        $this->view()->assign('form', $form);
        $this->view()->assign('message', $message);
        if ($this->editTemplate) {
            $this->view()->setTemplate($this->editTemplate);
        }
    }

    /**
     * Action to delete a block
     */
    public function deleteAction()
    {
        $result = $this->deleteBlock();
        $this->jump(array('action' => 'index'), $result['message']);
    }

    /**
     * Canonize POST data for block
     *
     * @param array $values
     *
     * @return array
     */
    protected function canonizePost(array $values)
    {
        return $values;
    }

    /**
     * Canonize block content
     *
     * @param string|string $content
     *
     * @return string
     */
    protected function canonizeContent($content)
    {
        return $content;
    }

    /**
     * Prepare values for form
     *
     * @param Row $blockRow
     *
     * @return array
     */
    protected function prepareFormValues($blockRow)
    {
        return $blockRow->toArray();
    }
}
