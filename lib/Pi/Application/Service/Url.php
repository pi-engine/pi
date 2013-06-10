<?php
/**
 * URL service
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
 * @package         Pi\Application
 * @subpackage      Service
 */

namespace Pi\Application\Service;

use Pi;
use Zend\Mvc\Router\RouteStackInterface;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Http\PhpEnvironment\Request;

class Url extends AbstractService
{
    /**
     * Router handler
     * @var RouteStackInterface
     */
    protected $router;

    /**
     * Set router
     *
     * @param RouteStackInterface $router
     * @return Url
     */
    public function setRouter(RouteStackInterface $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * Get router and load if not specified
     *
     * @return RouteStackInterface
     */
    public function getRouter()
    {
        if (!$this->router instanceof RouteStackInterface) {
            $this->router = Pi::engine()->application()->getRouter();
        }

        return $this->router;
    }

    /**
     * Generates an url given the name of a route.
     *
     * @see    Zend\Mvc\Router\RouteInterface::assemble()
     * @param  string  $route              Name of the route
     * @param  array   $params             Parameters for the link
     * @param  array   $options            Options for the route
     * @return string                   For the link href attribute
     */
    public function assemble($route = null, array $params = array(), $options = array())
    {
        if (!$this->getRouter()) {
            throw new \RuntimeException('No RouteStackInterface instance provided');
        }
        $options['name'] = $route ?: 'default';
        return $this->getRouter()->assemble($params, $options);
    }

    /**
     * Match a URL against routes and parse to paramters
     *
     * @param string $url
     * @return RouteMatch|null
     */
    public function route($url)
    {
        if (!$this->getRouter()) {
            throw new \RuntimeException('No RouteStackInterface instance provided');
        }
        $request = new Request();
        $request->setRequestUri($url);
        $result = $this->getRouter()->match($request);

        return $result;
    }
}
