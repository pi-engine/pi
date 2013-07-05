<?php
/**
 * Homepage route implementation
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
 * @subpackage      Router
 * @version         $Id$
 */

namespace Pi\Mvc\Router\Http;

use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Mvc\Router\Http\RouteInterface;
use Zend\Stdlib\RequestInterface as Request;

/**
 * Homepage route for Pi Engine
 *
 * Use cases:
 * 1. Full mode: pi-engine.url/system/index/index
 * 2. Partial mode: pi-engine.url/system/index; pi-engine.url/system
 * 3. Simple mode: pi-engine.url
 */
class Home extends Standard
{
    /**
     * Default values.
     *
     * @var array
     */
    protected $defaults = array(
        'module'        => 'system',
        'controller'    => 'index',
        'action'        => 'index'
    );

    /**
     * Matches homepage route
     *
     * @param  Request $request
     * @return RouteMatch
     */
    public function match(Request $request, $pathOffset = null)
    {
        if (!method_exists($request, 'getUri')) {
            return null;
        }

        $uri  = $request->getUri();
        $path = $uri->getPath();

        if ($pathOffset !== null) {
            $path = substr($path, $pathOffset);
        }
        $pathLength = strlen($path);
        $path = trim($path, $this->paramDelimiter);
        $matches = array();

        if (!empty($path)) {
            $params  = explode($this->paramDelimiter, $path);
            if (empty($params)
                    || empty($params[0])
                    || ($params[0] == 'system' && empty($params[1]))
                    || ($params[0] == 'system' && $params[1] == 'index' && empty($params[2]))
                    || ($params[0] == 'system' && $params[1] == 'index' && $params[2] == 'index')
                    ) {
            } else {
                return false;
            }
        }

        return new RouteMatch(array_merge($this->defaults, $matches), $pathLength);
    }

    /**
     * assemble(): Defined by Route interface.
     *
     * @see    Route::assemble()
     * @param  array $params
     * @param  array $options
     * @return mixed
     */
    public function assemble(array $params = array(), array $options = array())
    {
        return '/';
    }

    /**
     * getAssembledParams(): defined by Route interface.
     *
     * @see    Route::getAssembledParams
     * @return array
     */
    public function getAssembledParams()
    {
        return array();
    }
}
