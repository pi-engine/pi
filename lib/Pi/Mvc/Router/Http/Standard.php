<?php
/**
 * Default standard route implementation
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
use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\RequestInterface as Request;

/**
 * Default route for Pi Engine
 *
 * Use cases:
 * 1. Same structure, key-value and param delimiters:
 *    1.1 Full mode: /module/controller/action/key1/val1/key2/val2
 *    1.2 Full structure only: /module/controller/action
 *    1.3 Module with default structure: /module
 * 2. Same structure and param delimiters:
 *    2.1 Full mode: /module/controller/action/key1-val1/key2-val2
 *    2.2 Full structure only: /module/controller/action
 * 3. Different structure delimiter:
 *    3.1 Full mode: /module-controller-action/key1/val1/key2/val2; /module-controller-action/key1-val2/key2-val2
 *    3.2 Default structure and parameters: /module/key1/val1/key2/val2; /module/key1-val1/key2-val2
 *    3.3 Default structure: /module-controller
 */
class Standard implements RouteInterface
{
    /**
     * Path prefix
     * @var string
     */
    protected $prefix = '';

    /**
     * Delimiter between structured values of module, controller and action.
     *
     * @var string
     */
    protected $structureDelimiter;

    /**
     * Delimiter between keys and values.
     *
     * @var string
     */
    protected $keyValueDelimiter;

    /**
     * Delimtier before parameters.
     *
     * @var array
     */
    protected $paramDelimiter;

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
     * List of assembled parameters.
     *
     * @var array
     */
    protected $assembledParams = array();

    /**
     * Create a new wildcard route.
     *
     * @param  string $keyValueDelimiter
     * @param  string $paramDelimiter
     * @param  array  $defaults
     * @return void
     */
    public function __construct($prefix = null, $structureDelimiter = '/', $keyValueDelimiter = '/', $paramDelimiter = '/', array $defaults = array())
    {
        $this->prefix               = (null !== $prefix) ? $prefix : $this->prefix;
        $this->structureDelimiter   = $structureDelimiter;
        $this->keyValueDelimiter    = $keyValueDelimiter;
        $this->paramDelimiter       = $paramDelimiter;
        $this->defaults             = array_merge($this->defaults, $defaults);
    }

    /**
     * factory(): defined by Route interface.
     *
     * @see    Route::factory()
     * @param  array|Traversable $options
     * @return void
     */
    public static function factory($options = array())
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        } elseif (!is_array($options)) {
            throw new \InvalidArgumentException(__METHOD__ . ' expects an array or Traversable set of options');
        }

        if (!isset($options['route'])) {
            $options['route'] = null;
        }

        if (!isset($options['structure_delimiter'])) {
            $options['structure_delimiter'] = '/';
        }

        if (!isset($options['key_value_delimiter'])) {
            $options['key_value_delimiter'] = '/';
        }

        if (!isset($options['param_delimiter'])) {
            $options['param_delimiter'] = '/';
        }

        if (!isset($options['defaults'])) {
            $options['defaults'] = array();
        }

        return new static($options['route'], $options['structure_delimiter'], $options['key_value_delimiter'], $options['param_delimiter'], $options['defaults']);
    }

    /**
     * Get cleaned path
     *
     * @param Request $request
     * @param string $pathOffset
     * @return array
     */
    protected function canonizePath(Request $request, $pathOffset = null)
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

        if ($this->prefix) {
            $prefixLength = strlen($this->prefix);
            if ($this->prefix != substr($path, 0, $prefixLength)) {
                return null;
            }
            $path = substr($path, $prefixLength);
        }
        $path = trim($path, $this->paramDelimiter);

        return array($path, $pathLength);
    }

    /**
     * match(): defined by Route interface.
     *
     * @see    Route::match()
     * @param  Request $request
     * @return RouteMatch
     */
    public function match(Request $request, $pathOffset = null)
    {
        $result = $this->canonizePath($request, $pathOffset);
        if (null === $result) {
            return null;
        }
        list($path, $pathLength) = $result;

        $matches = array();
        $params  = $path ? explode($this->paramDelimiter, $path) : array();

        if ($this->paramDelimiter === $this->structureDelimiter) {
            foreach(array('module', 'controller', 'action') as $key) {
                if (!empty($params)) {
                    $matches[$key] = array_shift($params);
                }
            }
        } else {
            $mca = explode($this->structureDelimiter, $params[0]);
            foreach(array('module', 'controller', 'action') as $key) {
                if (!empty($mca)) {
                    $matches[$key] = array_shift($mca);
                }
            }
            array_shift($params);
        }

        if ($this->keyValueDelimiter === $this->paramDelimiter) {
            $count = count($params);

            for ($i = 0; $i < $count; $i += 2) {
                if (isset($params[$i + 1])) {
                    $matches[urldecode($params[$i])] = urldecode($params[$i + 1]);
                }
            }
        } else {
            foreach ($params as $param) {
                $param = explode($this->keyValueDelimiter, $param, 2);

                if (isset($param[1])) {
                    $matches[urldecode($param[0])] = urldecode($param[1]);
                }
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
        $mergedParams = array_merge($this->defaults, $params);
        if (!$mergedParams) {
            return $this->prefix;
        }

        $mca = array();
        foreach(array('module', 'controller', 'action') as $key) {
            if (isset($mergedParams[$key])) {
                $mca[$key] = urlencode($mergedParams[$key]);
                unset($mergedParams[$key]);
            }
        }

        $url = '';
        foreach ($mergedParams as $key => $value) {
            $url .= $this->paramDelimiter . urlencode($key) . $this->keyValueDelimiter . urlencode($value);
        }
        $url = ltrim($url, $this->paramDelimiter);
        if ($this->paramDelimiter === $this->structureDelimiter) {
            foreach(array('action', 'controller', 'module') as $key) {
                if (!empty($url) || $mca[$key] !== $this->defaults[$key]) {
                    $url = urlencode($mca[$key]) . $this->paramDelimiter . $url;
                }
            }
        } else {
            $structure = urlencode($mca['module']);
            if ($mca['controller'] !== $this->defaults['controller']) {
                $structure .= $this->structureDelimiter . urlencode($mca['controller']);
                if ($mca['action'] !== $this->defaults['action']) {
                    $structure .= $this->structureDelimiter . urlencode($mca['action']);
                }
            } elseif ($mca['action'] !== $this->defaults['action']) {
                $structure .= $this->structureDelimiter . urlencode($mca['controller']);
                $structure .= $this->structureDelimiter . urlencode($mca['action']);
            }
            $url = $structure . ($url ? $this->paramDelimiter . $url : '');
        }

        $prefix = $this->prefix ? $this->paramDelimiter . trim($this->prefix, $this->paramDelimiter) : '';
        return $prefix . $this->paramDelimiter . $url;
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
