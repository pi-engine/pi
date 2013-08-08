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
            $blocks = Pi::model('block_root')
                ->select(array('id' => array_keys($widgets)))->toArray();
            foreach ($blocks as $block) {
                $widgets[$block['id']]['block'] = $block;
            }
        }

        $available = array();
        $metaPath = Pi::service('module')->path($this->getModule()) . '/meta';
        $iterator = new \DirectoryIterator($metaPath);
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isFile()) {
                continue;
            }
            $name = $fileinfo->getFilename();
            $extension = pathinfo($name, PATHINFO_EXTENSION);
            if ('php' != $extension) {
                continue;
            }
            $name = pathinfo($name, PATHINFO_FILENAME);
            if (isset($installed[$name])
                || preg_match('/[^a-z0-9_\-]/', $name)) {
                continue;
            }
            $config = include $fileinfo->getPathname();
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
        $name = _filter($this->params('name'), 'regexp',
                        array('regexp' => '/^[a-z0-9_\-]+$/'));
        $meta = sprintf('%s/meta/%s.php',
                        Pi::service('module')->path($module), $name);
        $block = include $meta;
        $block['type'] = $this->type;
        $block['name'] = $name;
        if (empty($block['render'])) {
            $block['render'] = sprintf('Module\Widget\Render::%s', $name);
        } else {
            if (is_array($block['render'])) {
                $block['render'] = $block['render'][0] . '::'
                                 . $block['render'][1];
            }
            $block['render'] = sprintf('Module\Widget\Render\\%s',
                                       ucfirst($block['render']));
        }
        if (!isset($block['template'])) {
            $block['template'] = $name;
        }
        //$block['template'] = $name;
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
