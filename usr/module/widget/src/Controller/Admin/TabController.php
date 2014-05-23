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
 * For compound tabbed block
 */
class TabController extends ListController
{
    /**
     * {@inheritDoc}
     */
    protected $type = 'tab';

    /**
     * {@inheritDoc}
     */
    protected $editTemplate = 'widget-tab';

    /**
     * {@inheritDoc}
     */
    protected $formClass = 'BlockTabForm';

    /**
     * Load module list for block selection
     */
    protected function assignModules()
    {
        // Get block counts per module
        $model = Pi::model('block');
        $select = $model->select()->group('module')
            ->columns(array('count' => Pi::db()->expression('count(*)'), 'module'));
        $rowset = $model->selectWith($select);
        $blockCounts = array();
        foreach ($rowset as $row) {
            $blockCounts[$row->module] = $row->count;
        }

        // Get module list
        $modules = array();
        $moduleSet = Pi::model('module')->select(array('active' => 1));
        $widgetModule = array();
        foreach ($moduleSet as $row) {
            if ('widget' == $row->name) {
                $count = empty($blockCounts['widget']) ? '0' : $blockCounts['widget'];
                $widgetModule = array(
                    'name'  => $row->name,
                    'title' => $row->title . ' (' . $count . ')',
                );
            } elseif (!empty($blockCounts[$row->name])) {
                $modules[] = array(
                    'name'  => $row->name,
                    'title' => $row->title . ' (' . $blockCounts[$row->name] . ')',
                );
            }
        }
        array_unshift($modules, $widgetModule);

        $this->view()->assign('modules', $modules);
    }

    /**
     * {@inheritDoc}
     */
    public function addAction()
    {
        parent::addAction();
        $this->assignModules();
    }

    /**
     * AJAX method for getting blocks of a module
     *
     * @return array
     */
    public function blocklistAction()
    {
        // Module name
        $name = $this->params('name', '');

        $rowset = Pi::model('block')->select(array('module' => $name));
        $blocks = array();
        foreach ($rowset as $row) {
            if ('tab' == $row->type) {
                continue;
            }
            $blocks[] = array(
                'id'            => $row->id,
                'name'          => $row->name,
                'caption'       => $row->title,
                'description'   => $row->description,
            );
        }

        return array(
            'status'    => 1,
            'data'      => $blocks,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function editAction()
    {
        parent::editAction();
        $this->assignModules();
    }

    /**
     * {@inheritDoc}
     */
    protected function prepareContent($content)
    {
        $items = $content ? json_decode($content, true) : array();
        $items = array_filter($items);
        foreach ($items as &$item) {
            $item = array_merge(array(
                'id'        => 0,
                'caption'   => '',
                'link'      => '',
            ), $item);
        }
        $content = json_encode($items);

        return $content;
    }
}
