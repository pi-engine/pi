<?php
/**
 * Action controller class
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Module\Widget
 * @subpackage      Controller
 * @version         $Id$
 */

namespace Module\Widget\Controller\Admin;

use Pi;
use Module\Widget\Form\BlockTabForm as BlockForm;


/**
 * For compound tabbed block
 */
class TabController extends WidgetController
{
    protected $type = 'tab';

    protected function getForm()
    {
        return new BlockForm('block');
    }

    protected function assignModules()
    {
        $rowset = Pi::model('module')->select(array('active' => 1));
        $modules = array();
        foreach ($rowset as $row) {
            $modules[$row->id] = array(
                'name'  => $row->name,
                'title' => $row->title,
            );
        }
        $modules[0] = array(
            'name'  => '',
            'title' => __('Custom blocks'),
        );

        $this->view()->assign('modules', array_values($modules));
    }

    /**
     * List of widgets
     */
    public function indexAction()
    {
        $this->view()->assign('widgets', $this->widgetList());
        $this->view()->assign('title', __('Compound tab widgets'));
        $this->view()->setTemplate('list-tab');
    }

    /**
     * Add a block and default ACL rules
     */
    public function addAction()
    {
        parent::addAction();
        $this->view()->setTemplate('widget-tab');
        $this->assignModules();
    }

    /**
     * AJAX methdod for getting blocks of a module
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
                'caption'        => $row->title,
                'description'   => $row->description,
            );
        }

        return array(
            'status'    => 1,
            'data'      => $blocks,
        );
    }

    /**
     * Edit a block
     */
    public function editAction()
    {
        parent::editAction();
        $this->view()->setTemplate('widget-tab');
        $this->assignModules();
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
