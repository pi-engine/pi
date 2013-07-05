<?php
/**
 * Controller plugin URL class
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
 * @package         Pi\Mvc
 * @version         $Id$
 */

namespace Pi\Mvc\Controller\Plugin;

use Zend\EventManager\EventInterface;
use Zend\Mvc\Exception;
use Zend\Mvc\InjectApplicationEventInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\Router\RouteStackInterface;
use Zend\Mvc\Controller\Plugin\Url as ZendUrl;

class Url extends ZendUrl
{
    /**
     * Generates a URL based on a route
     *
     * @param  string $route RouteInterface name
     * @param  array $params Parameters to use in url generation, if any
     * @param  array|bool $options RouteInterface-specific options to use in url generation, if any. If boolean, and no fourth argument, used as $reuseMatchedParams
     * @param  boolean $reuseMatchedParams Whether to reuse matched parameters
     * @return string|Url
     */
    public function __invoke($route = null, array $params = array(), $options = array(), $reuseMatchedParams = false)
    {
        if (0 == func_num_args()) {
            return $this;
        }

        // Complete current module/controller
        if (!isset($params['module']) && isset($params['action'])) {
            $routeMatch = $this->getController()->getEvent()->getRouteMatch();
            $params['module'] = $routeMatch->getParam('module');
            if (!isset($params['controller'])) {
                $params['controller'] = $routeMatch->getParam('controller');
            }
        }

        $route = $route ?: null;
        $url = $this->fromRoute($route, $params, $options, $reuseMatchedParams);
        return $url;
    }
}
