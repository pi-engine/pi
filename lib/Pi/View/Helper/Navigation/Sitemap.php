<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         View
 */

namespace Pi\View\Helper\Navigation;

use Pi;
use Zend\View\Helper\Navigation\Sitemap as ZendSitemap;
use Zend\Navigation\AbstractContainer;

/**
 * Navigation sitemap helper
 *
 * {@inheritDoc}
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Sitemap extends ZendSitemap
{
    /**
     * Cache container
     * @var \StdClass
     */
    protected $cache;

    /**
     * Set cache container
     *
     * @param \StdClass $cache
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    /**
     * Renders helper
     *
     * @param  AbstractContainer $container
     *      [optional] container to render.
     *      Default is to render the container registered in the helper.
     * @return string
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
