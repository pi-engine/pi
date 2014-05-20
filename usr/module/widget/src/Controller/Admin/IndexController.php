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
        $paths = array(
            Pi::service('module')->path($this->getModule()) . '/meta',
            Pi::path('custom') . '/module/' . $this->getModule() . '/meta',
        );
        foreach ($paths as $metaPath) {
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
                    || preg_match('/[^a-z0-9_\-]/', $name)
                ) {
                    continue;
                }
                $config = include $fileinfo->getPathname();
                $config['name'] = $name;
                $available[$name] = $config;
            }
        }
        $list = array(
            'active'    => array_values($widgets),
            'available' => array_values($available),
        );

        $this->view()->assign('data', $list);
        $this->view()->setTemplate('ng');
    }

    /**
     * AJAX to install a widget
     */
    public function addAction()
    {
        $module = $this->getModule();
        $name = _filter(
            $this->params('name'),
            'regexp',
            array('regexp' => '/^[a-z0-9_\-]+$/')
        );
        $meta = sprintf(
            '%s/meta/%s.php',
            Pi::service('module')->path($module),
            $name
        );
        if (!file_exists($meta)) {
            $meta = sprintf(
                '%s/module/%s/meta/%s.php',
                Pi::path('custom'),
                $module,
                $name
            );
        }
        $block = include $meta;
        $block['type'] = $this->type;
        $block['name'] = $name;
        if (empty($block['render'])) {
            $block['render'] = sprintf('Module\Widget\Render::%s', $name);
        } else {
            if (is_array($block['render'])) {
                $block['render'] = $block['render'][0] . '::'
                                 . $block['render'][1];
                $class = $block['render'][0];
                $method = $block['render'][1];
            } else {
                list($class, $method) = explode('::', $block['render'], 2);
            }
            $renderClass = 'Custom\Widget\Render\\' . ucfirst($class);
            if (!class_exists($renderClass)) {
                $renderClass = 'Module\Widget\Render\\' . ucfirst($class);
            }
            $block['render'] = $renderClass . '::' . $method;
        }
        if (!isset($block['template'])) {
            $block['template'] = $name;
        }
        //$block['template'] = $name;
        $status = $this->addBlock($block);

        if ($status) {
            $message = sprintf(_a('The widget "%s" is installed.'), $name);
        } else {
            $message = sprintf(_a('The widget "%s" is not installed.'), $name);
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
