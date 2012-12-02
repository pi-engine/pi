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
use Pi\Mvc\Controller\ActionController;

/**
 * Script blocks
 */
class IndexController extends WidgetController
{
    protected $type = 'script';

    /**
     * List of widgets
     */
    public function indexAction()
    {
        $model = $this->getModel('widget');
        $rowset = $model->select(array('type' => $this->type));
        $widgets = array();
        $installed = array();
        foreach ($rowset as $row) {
            $widgets[$row->block] = $row->toArray();
            $installed[$row->name] = 1;
        }
        if ($widgets) {
            $blocks = Pi::model('block_root')->select(array('id' => array_keys($widgets)))->toArray();
            foreach ($blocks as $block) {
                $widgets[$block['id']]['block'] = $block;
            }
        }

        $available = array();
        $rootPath = Pi::service('module')->path($this->getModule()) . '/template/block';
        $iterator = new \DirectoryIterator($rootPath);
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isFile()) {
                continue;
            }
            $name = $fileinfo->getFilename();
            $extension = pathinfo($name, PATHINFO_EXTENSION);
            if ('phtml' != $extension) {
                continue;
            }
            $name = pathinfo($name, PATHINFO_FILENAME);
            if (isset($installed[$name]) || preg_match('/[^a-z0-9_]/i', $name)) {
                continue;
            }
            $meta = sprintf('%s/%s-config.php', $rootPath, $name);
            $config = array();
            if (is_readable($meta)) {
                $config = include $meta;
            } else {
                $config = array(
                    'title'         => $name,
                    'description'   => ''
                );
            }
            $config['name'] = $name;
            $available[$name] = $config;
        }
        $list = array(
            'active'    => $widgets,
            'available' => $available,
        );

        $this->view()->assign('widgets', $list);
        $this->view()->assign('title', __('Widget list'));
        $this->view()->setTemplate('list-script');
    }

    /**
     * AJAX to install a widget
     */
    public function addAction()
    {
        $module = $this->getModule();
        $name = $this->params('name');
        $meta = sprintf('%s/template/block/%s-config.php', Pi::service('module')->path($module), $name);
        $block = array();
        if (is_readable($meta)) {
            $block = include $meta;
        }
        $block['type'] = $this->type;
        $block['name'] = $name;
        if (empty($block['render'])) {
            $block['render'] = sprintf('Module\\Widget\\Render::%s', $name);
        } else {
            if (is_array($block['render'])) {
                $block['render'] = $block['render'][0] . '::' . $block['render'][1];
            }
            $block['render'] = sprintf('Module\\Widget\\Render\\%s', ucfirst($block['render']));
        }
        /*
        if (!isset($block['template'])) {
            $block['template'] = $name;
        }
        */
        $block['template'] = $name;
        $status = $this->addBlock($block);

        if ($status) {
            $message = sprintf(__('The widget "%s" is installed.'), $name);
        } else {
            $message = sprintf(__('The widget "%s" is not installed.'), $name);
        }
        return array(
            'status'    => $status,
            'message'   => $message,
        );
    }

    /**
     * AJAX to uninstall a widget
     */
    public function deleteAction()
    {
        return $this->deleteBlock();
    }
}
