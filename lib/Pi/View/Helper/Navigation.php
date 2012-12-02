<?php
/**
 * Navigation helper
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

namespace Pi\View\Helper;

use Pi;
use Zend\Navigation\Navigation as Container;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Helper\Navigation as NavigationHelper;
use Zend\View\Helper\Navigation\AbstractHelper as AbstractNavigationHelper;
use Zend\Navigation\Page\Mvc as MvcPage;
use Zend\Cache\Storage\Adapter\AbstractAdapter as CacheAdapter;

/**
 * Helper for loading navigation
 *
 * Usage inside a phtml template:
 * <code>
 *  $this->navigation()->render();
 *  $this->navigation('front')->render();
 * </code>
 */
class Navigation extends NavigationHelper
{
    /**
     * Cache container
     *
     * @var StdClass
     */
    protected $cache;

    /**
     * Load a navigation
     *
     * @param string    $name       navigation name
     * @param array     $options
     * @return  Navigation
     */
    public function __invoke($name = null, $options = array())
    {
        if (0 == func_num_args()) {
            return $this;
        }

        //  Sets the default router for MVC pages
        $router = Pi::engine()->application()->getRouter();
        $routeMatch = Pi::engine()->application()->getRouteMatch();
        MvcPage::setDefaultRouter($router);
        MvcPage::setDefaultRouteMatch($routeMatch);

        if (isset($options['cache_ttl'])) {
            $cacheNamespace = 'nav';
            $cacheTtl       = $options['cache_ttl'] ?: 86400;
            if (!empty($options['cache_id'])) {
                $cacheKey = $options['cache_id'];
            } else {
                $cacheKey = implode('-', array($name, $routeMatch->getParam('module'), $routeMatch->getParam('controller'), $routeMatch->getParam('action')));
                $cacheLevel = isset($options['cache_level']) ? $options['cache_level'] : '';
                $cacheKey = Pi::service('cache')->canonizeKey($cacheKey, $cacheLevel);
            }
            $cache          = clone Pi::service('cache')->storage();
            Pi::service('cache')->setNamespace($cacheNamespace, $cache);
            $this->cache = (object) array(
                'storage'   => $cache,
                'key'       => $cacheKey,
                'ttl'       => $cacheTtl,
            );
        } else {
            $this->cache = null;
        }

        /*
        //$template = empty($options['template']) ? 'navigation.phtml' : $options['template'];
        $cache = array();

        if (isset($options['cache_ttl'])) {
            $cacheKey           = 'nav_' . $name . (empty($options['cache_id']) ? '' : '_' . $options['cache_id']);
            $cacheStorage       = Pi::service('cache')->storage();
            $cache['ttl']       = empty($options['cache_ttl']) ? 86400 : $options['cache_ttl'];
            $cache['namespace'] = 'nav';
            $navConfig          = $cache['stroage']->getItem($cacheKey, $cache);
        }

        if (empty($navConfig)) {
            //$module = Pi::engine()->application()->getRouteMatch()->getParam('module');
            $module = Pi::service('module')->current();
            $navConfig = Pi::service('registry')->navigation->read($name, $module);
            if (!empty($cache)) {
                $cacheStorage->setItem($cacheKey, $navConfig, $cache);
            }
        } else {
            Pi::service('log')->debug(sprintf('Navigation "%s" is cached.', $name));
        }
        */

        $module = Pi::service('module')->current();
        $navConfig = Pi::service('registry')->navigation->read($name, $module);

        $this->setContainer(new Container($navConfig));

        return $this;
    }

    /**
     * Magic overload: Proxy to other navigation helpers or the container
     *
     * Examples of usage from a view script or layout:
     * <code>
     * // proxy to Menu helper and render container:
     * echo $this->navigation()->menu();
     *
     * // proxy to Breadcrumbs helper and set indentation:
     * $this->navigation()->breadcrumbs()->setIndent(8);
     *
     * // proxy to container and find all pages with 'blog' route:
     * $blogPages = $this->navigation()->findAllByRoute('blog');
     * </code>
     *
     * @param  string $method             helper name or method name in
     *                                    container
     * @param  array  $arguments          [optional] arguments to pass
     * @return mixed                      returns what the proxied call returns
     */
    public function __call($method, array $arguments = array())
    {
        // check if call should proxy to another helper
        $helper = $this->findHelper($method, false);
        if ($helper) {
            if (method_exists($helper, 'setCache')) {
                $helper->setCache($this->cache);
            }
            return call_user_func_array($helper, $arguments);
        }

        // default behaviour: proxy call to container
        return parent::__call($method, $arguments);
    }

    /**
     * Returns the helper matching $proxy
     *
     * The helper must implement the interface
     * {@link AbstractNavigationHelper}.
     *
     * @param string $proxy                        helper name
     * @param bool   $strict                       [optional] whether
     *                                             exceptions should be
     *                                             thrown if something goes
     *                                             wrong. Default is true.
     * @return AbstractNavigationHelper
     * @throws \Exception  if $strict is true and
     *         helper cannot be found
     * @throws \InvalidArgumentException if $strict is true and
     *         helper does not implement the specified interface
     */
    public function findHelper($proxy, $strict = true)
    {
        if (isset($this->helpers[$proxy])) {
            return $this->helpers[$proxy];
        }

        $class = __NAMESPACE__ . '\\Navigation\\' . ucfirst($proxy);
        if (!class_exists($class)) {
            $class = 'Zend\\View\\Helper\\Navigation\\' . ucfirst($proxy);
            if (!class_exists($class)) {
                if ($strict) {
                    throw new \RuntimeException('Failed to find a class to proxy to');
                }
                return false;
            }
        }
        $helper = new $class;

        if (!$helper instanceof AbstractNavigationHelper) {
            if ($strict) {
                throw new \InvalidArgumentException(sprintf(
                        'Proxy helper "%s" is not an instance of ' .
                        'Zend\View\Helper\Navigation\Helper',
                        get_class($helper)
                ));
            }

            return null;
        }

        if (!isset($this->injected[$class])) {
            $this->inject($helper);
            $this->injected[$class] = true;
        }

        $helper->setView($this->view);

        return $helper;
    }
}
