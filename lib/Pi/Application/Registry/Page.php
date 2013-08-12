<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Registry
 */

namespace Pi\Application\Registry;

use Pi;

/**
 * Page list
 *
 * Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Page extends AbstractRegistry
{
    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options = array())
    {
        $model = Pi::model('page');
        $pageList = $model->select(array(
            'section'   => $options['section'],
            'module'    => (string) $options['module']
        ));
        $pages = array();
        foreach ($pageList as $page) {
            list($module, $controller, $action) =
                array($page['module'], $page['controller'], $page['action']);
            $key = $page['module'];
            if (!empty($page['controller'])) {
                $key .= '-' . $page['controller'];
                if (!empty($page['action'])) {
                    $key .= '-' . $page['action'];
                }
            }
            $pages[$key] = $page['id'];
        }

        return $pages;
    }

    /**
     * {@inheritDoc}
     * @param string $section
     * @param string $module
     */
    public function read($section = 'front', $module = '')
    {
        $module = $module ?: Pi::service('module')->current();
        $options = compact('section', 'module');

        return $this->loadData($options);
    }

    /**
     * {@inheritDoc}
     * @param string        $section
     * @param string|null   $module
     */
    public function create($section = 'front', $module = '')
    {
        $module = $module ?: Pi::service('module')->current();
        $this->clear($module);
        $this->read($section, $module);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function clear($namespace = '')
    {
        Pi::registry('cache')->flush($namespace);
        Pi::registry('block')->flush($namespace);
        Pi::registry('resource')->flush($namespace);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function flush()
    {
        $this->clear('');
        $this->flushByModules();

        return $this;
    }
}
