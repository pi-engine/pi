<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Registry
 */

namespace Pi\Application\Registry;

use Pi;

/**
 * Page cache specs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class PageCache extends AbstractRegistry
{
    /**
     * {@inheritDoc}
     */
    protected function loadDynamic($options = [])
    {
        $modelPage = Pi::model('page');
        $cacheList = $modelPage->select(
            [
                'section' => $options['section'],
                'module'  => $options['module'],
                'cache_ttl >= 0',
            ]
        );
        $caches    = [];
        foreach ($cacheList as $cache) {
            $key = $cache['module'];
            if (!empty($cache['controller'])) {
                $key .= '-' . $cache['controller'];
                if (!empty($cache['action'])) {
                    $key .= '-' . $cache['action'];
                }
            }
            $caches[$key] = [
                'type'  => $cache['cache_type'],
                'ttl'   => $cache['cache_ttl'],
                'level' => $cache['cache_level'],
            ];
        }

        return $caches;
    }

    /**
     * {@inheritDoc}
     * @param string $module
     * @param string $section
     * @param string $type
     */
    public function read($module = '', $section = 'front', $type = 'action')
    {
        $module  = $module ?: Pi::service('module')->current();
        $options = compact('module', 'section', 'type');

        return $this->loadData($options);
    }

    /**
     * {@inheritDoc}
     * @param string $module
     * @param string $section
     */
    public function create($module = '', $section = 'front')
    {
        $this->read($module, $section);

        return true;
    }
}
