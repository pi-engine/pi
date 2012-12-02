<?php
/**
 * Feed route implementation
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
use Zend\Stdlib\RequestInterface as Request;

/**
 * Feed route for Pi Engine
 *
 * Use cases:
 * 1. Same structure, key-value and param delimiters:
 *    1.1 Full mode: feed/module/controller/action/key1/val1/key2/val2/atom-or-rss
 *    1.2 Full structure only: feed/module/controller/action/atom-or-rss
 *    1.3 Module with default structure: feed/module/atatom-or-rssom
 * 2. Same structure and param delimiters:
 *    2.1 Full mode: feed/module/controller/action/key1-val1/key2-val2/atom-or-rss
 *    2.2 Full structure only: feed/module/controller/action/atom-or-rss
 * 3. Different structure delimiter:
 *    3.1 Full mode: feed/module-controller-action/key1/val1/key2/val2/atom-or-rss; feed/module-controller-action/key1-val2/key2-val2/atom-or-rss
 *    3.2 Default structure and parameters: feed/module/key1/val1/key2/val2/atom-or-rss; feed/module/key1-val1/key2-val2/atom-or-rss
 *    3.3 Default structure: feed/module-controller/atom-or-rss
 */
class Feed extends Standard
{
    protected $prefix = '/feed';

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
        $matches = array();
        list($path, $pathLength) = $result;

        $params  = $path ? explode($this->paramDelimiter, $path) : array();

        // Get feed type
        $type = 'rss';
        if ($params) {
            $type = array_pop($params);
            if ('rss' != $type && 'atom' != $type) {
                $params[] = $type;
                $type = 'rss';
            }
        }
        $matches['type'] = $type;

        // Match regular params
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

        $type = '';
        if (isset($mergedParams['type'])) {
            $type = $mergedParams['type'];
            unset($mergedParams['type']);
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

        $url = $this->paramDelimiter . trim($this->prefix, $this->paramDelimiter) . ($url ? $this->paramDelimiter . $url : '');
        if ($type) {
            $url = rtrim($url, $this->paramDelimiter) . $this->paramDelimiter . $type;
        }

        return $url;
    }
}
