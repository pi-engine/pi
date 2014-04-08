<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         View
 */

namespace Pi\View\Helper;

use Pi;
use Zend\View\Helper\AbstractHelper;
use Pi\Mvc\Router\RouteMatch as RouteMatchHandler;

/**
 * Helper for loading parameters from routeMatch
 *
 * - Usage inside a phtml template
 *
 * ```
 *  $module = $this->routeMatch('module');
 *  $param  = $this->routeMatch('param');
 *  $routeMatch  = $this->routeMatch();
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class RouteMatch extends AbstractHelper
{
    /**
     * RouteInterface match returned by the router.
     *
     * @var RouteMatchHandler
     */
    protected $routeMatch;

    /**
     * Get params
     *
     * {@inheritdoc}
     */
    public function __invoke($name = null)
    {
        $routeMatch = Pi::service('url')->getRouteMatch();
        if (!func_num_args()) {
            return $routeMatch;
        }

        return $routeMatch->getParam($name);
    }
}
