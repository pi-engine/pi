<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         View
 */

namespace Pi\View\Helper;

use Pi;
use Pi\Navigation\Page\Mvc as MvcPage;
use Pi\Navigation\Navigation as Container;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Helper\Navigation as NavigationHelper;
use Zend\View\Helper\Navigation\AbstractHelper as AbstractNavigationHelper;

/**
 * Helper for loading navigation
 *
 * Usage inside a phtml template
 *
 * ```
 *  $this->navigation()->render();
 *  $this->navigation('front')->render();
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Navigation extends NavigationHelper
{
    /**
     * Cache container
     * @var \StdClass
     */
    protected $cache;

    /**
     * Load a navigation
     *
     * @param string|array  $name       Navigation name or config
     * @param array         $options    Options for navigation and caching
     * @return  self
     */
    public function __invoke($name = null, $options = array())
    {
        if (0 == func_num_args()) {
            return $this;
        }

        if (is_string($name)) {
            if (isset($options['cache_ttl'])) {
                $cacheNamespace = 'nav';
                $cacheTtl       = $options['cache_ttl'] ?: 86400;
                if (!empty($options['cache_id'])) {
                    $cacheKey = $options['cache_id'];
                } else {
                    $routeMatch = Pi::engine()->application()->getRouteMatch();
                    $cacheKey = implode(
                        '-',
                        array(
                            $name,
                            $routeMatch->getParam('module'),
                            $routeMatch->getParam('controller'),
                            $routeMatch->getParam('action')
                        )
                    );
                    $cacheLevel = isset($options['cache_level'])
                        ? $options['cache_level'] : '';
                    $cacheKey = Pi::service('cache')->canonizeKey(
                        $cacheKey,
                        $cacheLevel
                    );
                }
                $cache = clone Pi::service('cache')->storage();
                Pi::service('cache')->setNamespace($cacheNamespace, $cache);
                $this->cache = (object) array(
                    'storage'   => $cache,
                    'key'       => $cacheKey,
                    'ttl'       => $cacheTtl,
                );
            } else {
                $this->cache = null;
            }
            $module = Pi::service('module')->current();
            $section = isset($options['section'])
                ? $options['section'] : null;
            $navConfig = Pi::registry('navigation')->read(
                $name,
                $module,
                $section
            ) ?: array();
        } else {
            $navConfig = $name;
        }

        $this->setContainer($navConfig);

        return $this;
    }

    /**
     * Magic overload: Proxy to other navigation helpers or the container
     *
     * Examples of usage from a view script or layout
     *
     * ```
     * // proxy to Menu helper and render container:
     * echo $this->navigation()->menu();
     *
     * // proxy to Breadcrumbs helper and set indentation:
     * $this->navigation()->breadcrumbs()->setIndent(8);
     *
     * // proxy to container and find all pages with 'blog' route:
     * $blogPages = $this->navigation()->findAllByRoute('blog');
     * ```
     *
     * @param  string $method       helper name or method name in container
     * @param  array  $arguments    [optional] arguments to pass
     * @return mixed                returns what the proxied call returns
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
     * @param string $proxy     helper name
     * @param bool   $strict
     *      [optional] whether exceptions should be thrown
     *      if something goes wrong. Default is true.
     * @return AbstractNavigationHelper
     * @throws \Exception  if $strict is true and helper cannot be found
     * @throws \InvalidArgumentException
     *      if $strict is true and helper does not implement
     *      the specified interface
     */
    public function findHelper($proxy, $strict = true)
    {
        if (isset($this->helpers[$proxy])) {
            return $this->helpers[$proxy];
        }

        $class = __NAMESPACE__ . '\Navigation\\' . ucfirst($proxy);
        if (!class_exists($class)) {
            $class = 'Zend\View\Helper\Navigation\\' . ucfirst($proxy);
            if (!class_exists($class)) {
                if ($strict) {
                    throw new \RuntimeException(
                        'Failed to find a class to proxy to'
                    );
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
        } elseif ($this->getInjectContainer() && !$helper->hasContainer()) {
            $helper->setContainer($this->getContainer());
        }

        $helper->setView($this->view);

        return $helper;
    }

    /**
     * Set navigation data container
     *
     * Register default router and RouteMatch to MvcPage
     *
     * @param Container $container
     * @return self
     */
    public function setContainer($container = null)
    {
        //  Sets the default router for MVC pages
        $router = Pi::engine()->application()->getRouter();
        $routeMatch = Pi::engine()->application()->getRouteMatch();
        MvcPage::setDefaultRouter($router);
        MvcPage::setDefaultRouteMatch($routeMatch);

        if (is_array($container)) {
            $container = new Container($container);
        }

        parent::setContainer($container);

        return $this;
    }
}
