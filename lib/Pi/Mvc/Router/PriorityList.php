<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Mvc\Router;

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
        if (null === $this->extraRoutes && $this->extraRouteLoader) {
            $this->extraRoutes = call_user_func($this->extraRouteLoader);
        }
        $route = null;
        if ($this->extraRoutes && isset($this->extraRoutes[$name])) {
            $route = $this->extraRoutes[$name];
        }

        return $route;
    }

    /**
     * Set loader for extra routes which do not belong to current section
     *
     * @param Closure $loader
     *
     * @return $this
     */
    public function setExtraRouteLoader($loader)
    {
        $this->extraRouteLoader = $loader;

        return $this;
    }
}
