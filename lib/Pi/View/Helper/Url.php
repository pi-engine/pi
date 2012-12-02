<?php
/**
 * URL builder helper
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
use Zend\View\Helper\AbstractHelper;
use Zend\Mvc\Router\RouteStackInterface;
use Pi\Mvc\Router\RouteMatch;
use Zend\View\Helper\Url as ZendUrl;

/**
 * Helper for assembling URL with routes and parameters
 *
 * Usage inside a phtml template:
 * <code>
 *  $this->url('home');
 *  $this->url('default', array('module' => 'demo', 'controller' => 'test');
 * </code>
 */
class Url extends ZendUrl
{
    /**
     * RouteStackInterface instance.
     *
     * @var RouteStackInterface
     */
    protected $router;

    /**
     * RouteInterface match returned by the router.
     *
     * @var RouteMatch.
     */
    protected $routeMatch;

    /**
     * Generates an url given the name of a route.
     *
     * @see    Zend\Mvc\Router\RouteInterface::assemble()
     * @param  string  $name               Name of the route
     * @param  array   $params             Parameters for the link
     * @param  array   $options            Options for the route
     * @param  boolean $reuseMatchedParams Whether to reuse matched parameters
     * @return string Url                  For the link href attribute
     * @throws \RuntimeException  If no RouteStackInterface was provided
     * @throws \RuntimeException  If no RouteMatch was provided
     * @throws \RuntimeException  If RouteMatch didn't contain a matched route name
     */
    public function __invoke($name = null, array $params = array(), $options = array(), $reuseMatchedParams = false)
    {
        if (null === $this->router()) {
            throw new \RuntimeException('No RouteStackInterface instance provided');
        }

        if (!$name) {
            if ($this->routeMatch() === null) {
                throw new \RuntimeException('No RouteMatch instance provided');
            }

            $name = $this->routeMatch()->getMatchedRouteName();

            if ($name === null) {
                throw new \RuntimeException('RouteMatch does not contain a matched route name');
            }
        }

        // Complete current module/controller
        if (!isset($params['module']) && isset($params['action'])) {
            $routeMatch = $this->routeMatch();
            $params['module'] = $routeMatch->getParam('module');
            if (!isset($params['controller'])) {
                $params['controller'] = $routeMatch->getParam('controller');
            }
        }

        if ($reuseMatchedParams && $this->routeMatch() !== null) {
            $params = array_merge($this->routeMatch()->getParams(), $params);
        }

        $options['name'] = $name;

        return $this->router()->assemble($params, $options);
    }


    /**
     * Get the router to use for assembling.
     *
     * @return RouteStackInterface $router
     */
    public function router()
    {
        if (!$this->router) {
            $this->router = Pi::engine()->application()->getRouter();
        }
        return $this->router;
    }

    /**
     * Get route match returned by the router.
     *
     * @return  RouteMatch $routeMatch
     */
    public function routeMatch()
    {
        if (!$this->routeMatch) {
            $this->routeMatch = Pi::engine()->application()->getRouteMatch();
        }
        return $this->routeMatch;
    }
}
