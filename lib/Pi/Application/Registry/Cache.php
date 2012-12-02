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

/**
 * Page cache specs
 */
class Cache extends AbstractRegistry
{
    protected function loadDynamic($options = array())
    {
        $modelPage = Pi::model('page');
        $cacheList = $modelPage->select(array(
            'section'   => $options['section'],
            'module'    => $options['module'],
            'cache_ttl >= 0'
        ));
        $caches = array();
        foreach ($cacheList as $cache) {
            $key = $cache['module'];
            if (!empty($cache['controller'])) {
                $key .= '-' . $cache['controller'];
                if (!empty($cache['action'])) {
                    $key .= '-' . $cache['action'];
                }
            }
            $caches[$key] = array('ttl' => $cache['cache_ttl'], 'level' => $cache['cache_level']);
        }

        return $caches;
    }

    public function read($module, $section, $type = 'action')
    {
        $options = compact('module', 'section', 'type');
        return $this->loadData($options);
    }

    public function create($module, $section)
    {
        $this->read($module, $section);
        return true;
    }
}
