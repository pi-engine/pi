<?php
/**
 * Navigation sitemap helper
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
 * @package         Pi\View
 * @subpackage      Helper
 * @version         $Id$
 */

namespace Pi\View\Helper\Navigation;

use Pi;
use Zend\View\Helper\Navigation\Sitemap as ZendSitemap;
use Zend\Cache\Storage\Adapter\AbstractAdapter as CacheAdapter;

class Sitemap extends ZendSitemap
{
    /**
     * Cache container
     *
     * @var StdClass
     */
    protected $cache;

    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    /**
     * Renders helper
     *
     * Implements {@link HelperInterface::render()}.
     *
     * @param  link|AbstractContainer $container [optional] container to render. Default is
     *                              to render the container registered in the
     *                              helper.
     * @return string               helper output
     */
    public function render($container = null)
    {
        Pi::service('log')->start('sitemap');

        // Try to load from cache
        if ($this->cache) {
            $cacheKey = $this->cache->key . '-sp';
            $content = $this->cache->storage->getItem($cacheKey);
            if (null !== $content) {
                if (Pi::service()->hasService('log')) {
                    Pi::service('log')->info('Sitemap is loaded from cache.');
                }
                return $content;
            }
        }

        // Generate if no cache available
        $content = parent::render($container);

        // Save to cache
        if ($this->cache) {
            $this->cache->storage->setItem($cacheKey, $content);
        }

        Pi::service('log')->end('sitemap');

        return $content;
    }
}