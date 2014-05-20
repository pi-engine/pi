<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Search\Route;

use Pi\Mvc\Router\Http\Standard;

/**
 * Search route
 *
 * Use cases
 *
 * - Normal query
 *   - /search?q=<query>
 *
 * - Module query
 *   - /search?m=<module>&q=<query>
 *   - /search/<module>?q=<query>
 *
 * - External query
 *   - /search?s=<service>&q=<query>
 *   - /search/service/<service>?q=<query>
 *
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Search extends Standard
{
    /**
     * Default values.
     * @var array
     */
    protected $defaults = array(
        'module'        => 'search',
        'controller'    => 'index',
        'action'        => 'index'
    );

    /**
     * {@inheritDoc}
     */
    protected function parse($path)
    {
        $matches = null;

        $parts = array();
        if ($path) {
            $parts = array_filter(explode($this->structureDelimiter, $path));
        }
        if ($parts) {
            $matches = array();
            $matches['controller'] = 'index';
            $term = array_shift($parts);
            if ('service' == $term) {
                $matches['controller']  = 'index';
                $matches['action']      = 'service';
                if ($parts) {
                    $matches['service'] = $parts[0];
                }
            } else {
                $matches['action'] = 'module';
                $matches['m'] = $term;
            }

            if ($parts) {
                $matches = array_merge(
                    (array) $matches,
                    $this->parseParams($parts)
                );
            }
        }

        if (null !== $matches) {
            $matches = array_merge($this->defaults, $matches);
        } else {
            //$path = $this->defaults['module'] . $this->structureDelimiter . $path;
            $path = $this->prefix . $this->structureDelimiter . $path;
            $matches = parent::parse($path);
        }

        return $matches;
    }

    /**
     * {@inheritDoc}
     */
    protected function assembleParams(array $params)
    {
        $url = '';
        if ($this->defaults['controller'] != $params['controller']) {
            foreach ($params as $key => $value) {
                if (in_array($key, array('module', 'controller', 'action'))) {
                    continue;
                }
                if (null === $value || '' === $value) {
                    continue;
                }
                $url .= $this->paramDelimiter . $this->encode($key)
                    . $this->keyValueDelimiter . $this->encode($value);
            }
            $url = ltrim($url, $this->paramDelimiter);
        }

        return $url;
    }

    /**
     * {@inheritDoc}
     */
    protected function assembleStructure(array $params, $url = '')
    {
        $mca    = array();
        foreach (array('controller', 'action') as $key) {
            if (!empty($params[$key])) {
                $mca[$key] = $this->encode($params[$key]);
            }
        }
        $query  = array();
        if ($this->defaults['controller'] == $params['controller']) {
            if (!empty($params['m'])) {
                $mca['controller'] = $params['m'];
                $mca['action'] = $this->defaults['action'];
            } elseif (!empty($params['s']) || !empty($params['service'])) {
                $service = !empty($params['s']) ? $params['s'] : $params['service'];
                $mca['controller'] = 'service';
                $mca['action'] = $service;
            }
            if (!empty($params['q'])) {
                $query['q'] = $params['q'];
            } elseif (!empty($params['query'])) {
                $query['q'] = $params['query'];
            }
        }

        if ($this->paramDelimiter === $this->structureDelimiter) {
            foreach(array('action', 'controller') as $key) {
                if (!empty($url) || $mca[$key] !== $this->defaults[$key]) {
                    $url = $this->encode($mca[$key]) . $this->paramDelimiter
                        . $url;
                }
            }
        } else {
            $structure = '';
            if ($mca['controller'] !== $this->defaults['controller']) {
                $structure .= $this->structureDelimiter
                    . $this->encode($mca['controller']);
                if ($mca['action'] !== $this->defaults['action']) {
                    $structure .= $this->structureDelimiter
                        . $this->encode($mca['action']);
                }
            } elseif ($mca['action'] !== $this->defaults['action']) {
                $structure .= $this->structureDelimiter
                    . $this->encode($mca['controller']);
                $structure .= $this->structureDelimiter
                    . $this->encode($mca['action']);
            }
            $url = $structure . ($url ? $this->paramDelimiter . $url : '');
        }
        if ($query) {
            $url .= '?' . http_build_query($query);
        }

        return $url;
    }

}