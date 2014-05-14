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

/**
 * For static block
 */
class StaticController extends WidgetController
{
    protected $type = 'html';

    /**
     * {@inheritDoc}
     */
    protected $editTemplate = 'system:component/form';

    /**
     * {@inheritDoc}
     */
    protected $formClass = 'BlockStaticForm';

    /**
     * Get content type list
     *
     * @return array
     */
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
     * {@inheritDoc}
     */
    protected function widgetList()
    {
        $model = $this->getModel('widget');
        $contentTypes = $this->contentTypes();
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

        return $widgets;
    }

    /**
     * {@inheritDoc}
     */
    public function addAction()
    {
        if ($this->request->isPost()) {
            $this->type = $this->request->getPost('type');
        } else {
            $this->type = $this->params('type', 'html');
        }
        parent::addAction();
    }

    /**
     * {@inheritDoc}
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
        parent::editAction();
    }
}
