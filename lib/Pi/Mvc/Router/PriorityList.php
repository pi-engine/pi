<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Mvc\Router;

use Closure;
use Zend\Mvc\Router\PriorityList as ZendPriorityList;
use Zend\Mvc\Router\RouteInterface;

/**
 * {@inheritDoc}
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class PriorityList extends ZendPriorityList
{
    /**
     * Internal list of all extra routes.
     *
     * @var array
     */
    protected $extraRoutes = null;

    /** @var  Closure Extra routes loader */
    protected $extraRouteLoader;

    /**
     * {@inheritDoc}
     */
    public function get($name)
    {
        $route = parent::get($name);
        if (!$route) {
            $route = $this->loadExtraRoute($name);
        }

        return $route;
    }

    /**
     * Load an extra route
     *
     * @param string $name
     *
     * @return RouteInterface
     */
    protected function loadExtraRoute($name)
    {
        $extraRoutes = $this->loadExtraRoutes();
        $route = isset($extraRoutes[$name]) ? $extraRoutes[$name] : null;

        return $route;
    }

    /**
     * Set loader for extra routes which do not belong to current section
     *
     * @param Closure|array $loader
     *
     * @return $this
     */
    public function setExtraRouteLoader($loader)
    {
        $this->extraRouteLoader = $loader;

        return $this;
    }

    /**
     * Load extra routes
     *
     * @param bool $reset
     *
     * @return array
     */
    public function loadExtraRoutes($reset = false)
    {
        if (($reset || null === $this->extraRoutes)
            && $this->extraRouteLoader
        ) {
            $this->extraRoutes = call_user_func($this->extraRouteLoader);
        }

        return (array) $this->extraRoutes;
    }
}
