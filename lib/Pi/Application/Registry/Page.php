<?php
/**
 * Pi cache registry
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
 * @package         Pi\Application
 * @subpackage      Registry
 * @version         $Id$
 */

namespace Pi\Application\Registry;
use Pi;

class Page extends AbstractRegistry
{
    protected function loadDynamic($options = array())
    {
        $model = Pi::model('page');
        $pageList = $model->select(array(
            'section'   => $options['section'],
            'module'    => (string) $options['module']
        ));
        $pages = array();
        foreach ($pageList as $page) {
            list($module, $controller, $action) = array($page['module'], $page['controller'], $page['action']);
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

    public function read($section, $module = null)
    {
        $options = compact('section', 'module');
        return $this->loadData($options);
    }

    public function create($section, $module = null)
    {
        $this->clear($module);
        $this->read($section, $module);
        return true;
    }

    public function clear($namespace = '')
    {
        Pi::service('registry')->cache->flush($namespace);
        Pi::service('registry')->block->flush($namespace);
        Pi::service('registry')->resource->flush($namespace);
        return $this;
    }

    public function flush()
    {
        $this->clear('');
        $this->flushByModules();
        return $this;
    }
}
