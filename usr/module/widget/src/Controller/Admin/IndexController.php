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
    /**
     * {@inheritDoc}
     */
    protected $type = 'script';

    /**
     * {@inheritDoc}
     */
    public function indexAction()
    {
        $widgets = $this->widgetList();
        $installed = array();
        foreach ($widgets as $key => $block) {
            $installed[$block['name']] = 1;
        }

        $available = array();
        $paths = array(
            Pi::service('module')->path($this->getModule()) . '/meta',
            Pi::path('custom') . '/module/' . $this->getModule() . '/meta',
        );
        foreach ($paths as $metaPath) {
            $filter = function ($fileinfo) use (&$available, $installed) {
                if (!$fileinfo->isFile()) {
                    return false;
                }
                $name = $fileinfo->getFilename();
                $extension = pathinfo($name, PATHINFO_EXTENSION);
                if ('php' != $extension) {
                    return false;
                }
                $name = pathinfo($name, PATHINFO_FILENAME);
                if (isset($installed[$name])
                    || preg_match('/[^a-z0-9_\-]/', $name)
                ) {
                    return false;
                }
                $config = include $fileinfo->getPathname();
                $config['name'] = $name;
                $available[$name] = $config;
            };
            Pi::service('file')->getList($metaPath, $filter);
        }
        $list = array(
            'active'    => array_values($this->widgetList()),
            'available' => array_values($available),
        );

        $this->view()->assign('data', $list);
        $this->view()->setTemplate('ng');
    }

    /**
     * {@inheritDoc}
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
                /*
                $block['render'] = $block['render'][0] . '::'
                                 . $block['render'][1];
                */
                $class = $block['render'][0];
                $method = $block['render'][1];
            } elseif (strpos('::', $block['render'])) {
                list($class, $method) = explode('::', $block['render'], 2);
            } else {
                $class = $block['render'];
                $method = 'render';
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
     * {@inheritDoc}
     */
    public function deleteAction()
    {
        return $this->deleteBlock();
    }
}
