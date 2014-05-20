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

/**
 * For static block
 */
abstract class WidgetController extends ActionController
{
    protected $type;

    protected function getForm()
    {}

    protected function addBlock(array $block)
    {
        $status = 0;
        $module = $this->getModule();
        $block['module'] = $module;

        if (!isset($block['content'])) {
            $block['content'] = '';
        }
        $widgetMeta = $block['content'];
        $block['content'] = $this->canonizeContent($block['content']);

        $result = Pi::api('block', $module)->add($block, $block['type']);
        $id = $result['root'];
        if ($id) {
            $widget = array(
                'block' => $id,
                'name'  => $block['name'],
                'meta'  => $widgetMeta,
                'type'  => $this->type,
                'time'  => time(),
            );
            $row = $this->getModel('widget')->createRow($widget);
            $row->save();
            if ($row->id) {
                $status = 1;
                Pi::registry('block')->clear($module);
            }
        }

        return $status;
    }

    protected function updateBlock($widgetRow, $block)
    {
        $widgetMeta = $block['content'];
        $block['content'] = $this->canonizeContent($block['content']);
        if (isset($block['type'])) {
            unset($block['type']);
        }

        $result = Pi::api('block', 'system')->update(
            $widgetRow->block,
            $block
        );
        $status = $result['status'];
        if ($status) {
            $widgetRow->name = $block['name'];
            $widgetRow->meta = $widgetMeta;
            $widgetRow->time = time();
            $widgetRow->save();
        }

        return $status;
    }

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
            extract($result);
            if ($status) {
                $row->delete();
                Pi::registry('block')->clear($this->getModule());
                $message = sprintf(
                    _a('The widget "%s" is uninstalled.'),
                    $row->name
                );
            } else {
                $message = sprintf(
                    _a('The widget "%s" is not uninstalled.'),
                    $row->name
                );
            }
        }

        return array(
            'status'    => $status,
            'message'   => $message,
        );
    }

    protected function processPost($form)
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
                    ? $values['type'] : $this->type;
                $status = $this->addBlock($values);
            }

            if (!$status) {
                $status = -1;
            }
        }

        return $status;
    }

    /**
     * Widget list
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
    protected function addAction()
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
            //$content = $content ? json_decode($content, true) : array();
        } else {
            $content = '';
            $message = '';
        }

        $this->view()->assign('form', $form);
        $this->view()->assign('content', $content);
        $this->view()->assign('message', $message);
        $this->view()->assign('title', _a('Add a block'));
    }

    /**
     * Edit a block
     */
    protected function editAction()
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
    }

    public function deleteAction()
    {}

    protected function canonizePost($values)
    {
        return $values;
    }

    protected function canonizeContent($content)
    {
        return $content;
    }

    protected function prepareFormValues($blockRow)
    {
        return $blockRow->toArray();
    }
}
