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
            'widgets' => $this->widgetList()
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
     * Add a widget
     *
     * @param array $block
     *
     * @return int
     */
    protected function add(array $block)
    {
        $status = 0;
        $module = $this->getModule();
        $block['module'] = $module;

        if (empty($block['type'])) {
            $block['type'] = $this->type;
        }
        $id = Pi::api('block', $module)->add($block);
        if ($id) {
            $status = 1;
            Pi::registry('block')->clear($module);
        }

        return $status;
    }

    /**
     * Update a widget and affiliated blocks
     *
     * @param int   $id
     * @param array $block
     *
     * @return bool
     */
    protected function update($id, array $block)
    {
        $status = false;
        if (isset($block['type'])) {
            unset($block['type']);
        }
        $blockId = $this->updateWidget($id, $block);
        if (!$blockId) {
            return $status;
        }
        $result = $this->updateBlock($blockId, $block);
        $status = $result['status'];

        return $status;
    }

    /**
     * Update a widget
     *
     * @param int   $id
     * @param array $block
     *
     * @return int
     */
    protected function updateWidget($id, array $block)
    {
        $result = 0;
        $row = $this->getModel('widget')->find($id);
        if (!$row) {
            return $result;
        }
        $row->name = $block['name'];
        $row->meta = isset($block['meta'])
            ? $block['meta']
            : $block['content'];
        $row->time = time();
        $result = $row->save() ? $row->block : 0;

        return $result;
    }

    /**
     * Update a block
     *
     * @param int $id
     * @param array $block
     *
     * @return array
     */
    protected function updateBlock($id, array $block)
    {
        return Pi::api('block', 'system')->update($id, $block);
    }

    /**
     * Delete a widget and affiliated blocks
     *
     * @param int   $id
     *
     * @return bool
     */
    protected function delete($id)
    {
        $status = false;
        $blockId = $this->deleteWidget($id);
        if (!$blockId) {
            return $status;
        }
        $result = $this->deleteBlock($blockId);
        $status = $result['status'];

        return $status;
    }

    /**
     * Delete a widget
     *
     * @param int $id
     *
     * @return int
     */
    protected function deleteWidget($id)
    {
        $result = 0;
        $row = $this->getModel('widget')->find($id);
        if (!$row) {
            return $result;
        }
        $result = $row->delete() ? $row->block : 0;

        return $result;
    }

    /**
     * Delete a block
     *
     * @param int $id
     *
     * @return array
     */
    protected function deleteBlock($id)
    {
        return Pi::api('block', 'system')->delete($id, true);
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
                $status = $this->update($id, $values);
            } else {
                $values['type'] = !empty($values['type'])
                    ? $values['type']
                    : $this->type;
                $status = $this->add($values);
            }

            if (!$status) {
                $status = -1;
            }
        }

        return $status;
    }

    /**
     * Get widget list
     *
     * @param array|null $widgets
     *
     * @return array
     */
    protected function widgetList($widgets = null)
    {
        if (null === $widgets) {
            $model = $this->getModel('widget');
            $rowset = $model->select(array('type' => $this->type));
            $widgets = array();
            foreach ($rowset as $row) {
                $widgets[$row->block] = $row->toArray();
            }
        }
        $list = array();
        if ($widgets) {
            $blocks = Pi::model('block_root')->select(
                array('id' => array_keys($widgets))
            )->toArray();
            foreach ($blocks as $block) {
                $item = $widgets[$block['id']];
                $item['block'] = $block;
                $list[] = $item;
            }
        }
        array_walk($list, function (&$item) {
            $item['editUrl'] = $this->url('', array(
                'action'    => 'edit',
                'id'        => $item['id']
            ));
            $item['deleteUrl'] = $this->url('', array(
                'action'    => 'delete',
                'id'        => $item['id']
            ));
        });

        return $list;
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
            $content = $this->prepareContent($row->meta);

            $blockRow = Pi::model('block_root')->find($row->block);
            $values = $this->prepareFormValues($blockRow);
            $values['content'] = $content;
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
        $id = $this->params('id');
        $result = $this->delete($id);
        if ($result) {
            $message = _a('The widget is removed.');
            //Pi::registry('block')->clear($this->getModule());
        } else {
            $message =  _a('The widget is not removed.');
        }

        $this->jump(array('action' => 'index'), $message);
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

    /**
     * Prepare content for edit
     *
     * @param string $content
     *
     * @return string
     */
    protected function prepareContent($content)
    {
        return $content;
    }
}
