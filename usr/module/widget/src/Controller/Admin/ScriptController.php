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
class ScriptController extends WidgetController
{
    /**
     * {@inheritDoc}
     */
    protected $type = 'script';

    /**
     * {@inheritDoc}
     */
    public function addAction()
    {
        $widgets = $this->widgetList();
        $installed = array();
        foreach ($widgets as $block) {
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
                $config['installUrl'] = $this->url('', array(
                    'action'    => 'install',
                    'name'      => $name,
                ));
                $available[] = $config;
            };
            Pi::service('file')->getList($metaPath, $filter);
        }
        $data = array(
            'available' => $available,
        );

        $this->view()->assign('data', $data);
        $this->view()->setTemplate('ng-script');
    }

    /**
     * {@inheritDoc}
     */
    public function installAction()
    {
        $module = $this->getModule();
        $name = $this->params('name');
        $name = _filter(
            $name,
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
        $status = $this->add($block);

        if ($status) {
            $message = sprintf(_a('The widget "%s" is installed.'), $name);
        } else {
            $message = sprintf(_a('The widget "%s" is not installed.'), $name);
        }

        $this->jump(array('action' => 'add'), $message);

        /*
        return array(
            'status'    => $status,
            'message'   => $message,
        );
        */
    }

    /**
     * {@inheritDoc}
     */
    protected function widgetList($widgets = null)
    {
        $widgets = parent::widgetList($widgets);
        array_walk($widgets, function (&$item) {
            $item['editUrl'] = null;
        });

        return $widgets;
    }
}
