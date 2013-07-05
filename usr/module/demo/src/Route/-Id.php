<?php
/**
 * Demo route implementation
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
 * @package         Module\Demo
 * @subpackage      Route
 * @version         $Id$
 */

namespace Module\Demo\Route;

use Pi\Mvc\Router\Http\Standard;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Stdlib\RequestInterface as Request;

/**
 * Route: url/$id
 */
class Id extends Standard
{
    protected $prefix = 'demo/';

    /**
     * Default values.
     *
     * @var array
     */
    protected $defaults = array(
        'module'        => 'demo',
        'controller'    => 'route',
        'action'        => 'id'
    );

    public function match(Request $request, $pathOffset = null)
    {
        $result = $this->canonizePath($request, $pathOffset);
        if (null === $result) {
            return null;
        }
        list($path, $pathLength) = $result;
        if (!is_int($path)) {
            return null;
        }

        $matches = array(
            'action'        => 'id',
            'id'            => intval($path),
        );

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
        $mergedParams = array_merge($this->defaults, $params);
        if (!$mergedParams || !isset($mergedParams['id'])) {
            return $this->prefix;
        }

        return $this->paramDelimiter . trim($this->prefix, $this->paramDelimiter) . $this->paramDelimiter . intval($mergedParams['id']);
    }
}
