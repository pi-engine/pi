<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Mvc\Router\Http;

use Pi;
use Laminas\Mvc\Router\Http\RouteMatch;
use Laminas\Stdlib\RequestInterface as Request;

/**
 * Homepage route for Pi Engine
 *
 * Use cases:
 *
 * 1. Full mode: pi-engine.url/system/index/index
 * 2. Partial mode: pi-engine.url/system/index; pi-engine.url/system
 * 3. Simple mode: pi-engine.url
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Home extends Standard
{
    /**
     * Default values.
     *
     * @var array
     */
    protected $defaults
        = [
            'module'     => 'system',
            'controller' => 'index',
            'action'     => 'index',
        ];

    /**
     * Matches homepage route
     *
     * @param Request  $request
     * @param int|null $pathOffset
     *
     * @return RouteMatch|null
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
        $path       = trim($path, $this->paramDelimiter);
        $matches    = [];

        if (!empty($path)) {
            $params = explode($this->paramDelimiter, $path);
            if (empty($params)
                || empty($params[0])
                || ($params[0] == 'system' && empty($params[1]))
                || ($params[0] == 'system' && $params[1] == 'index'
                    && empty($params[2])
                )
                || ($params[0] == 'system' && $params[1] == 'index'
                    && $params[2] == 'index'
                )
            ) {
            } else {
                return false;
            }
        }

        return new RouteMatch(
            array_merge($this->defaults, $matches),
            $pathLength
        );
    }

    /**
     * assemble(): Defined by Route interface.
     *
     * @param array $params
     * @param array $options
     *
     * @return string
     * @see    Route::assemble()
     */
    public function assemble(array $params = [], array $options = [])
    {
        if (isset($params['section'])) {
            $section = $params['section'];
        } else {
            $section = Pi::engine()->application()->getSection();
        }
        if ('admin' == $section) {
            $url = Pi::service('url')->getRouter()->getRoute('admin')->assemble(
                [
                    'module'     => 'system',
                    'controller' => 'index',
                    'action'     => 'index',
                ], $options
            );
        } else {
            $url = '/';
        }

        return $url;
    }

    /**
     * getAssembledParams(): defined by Route interface.
     *
     * @return array
     * @see    Route::getAssembledParams
     */
    public function getAssembledParams()
    {
        return [];
    }
}
