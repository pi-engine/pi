<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Page\Route;

use Pi\Mvc\Router\Http\Standard;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Stdlib\RequestInterface as Request;

/**
 * Route for pages
 *
 *  1. ID: /url/page/123
 *  2. Slug: /url/page/my-slug
 *  3. Name: /url/page/name
 *
 * Route for pages with page setup, for instance blocks and cache
 *  1. ID: /url/page/123/type/<type>
 *  2. Slug: /url/page/my-slug/type/<type>
 *  3. Name: /url/page/name/type/<type>
 *
 */
class Page extends Standard
{
    /**
     * {@inheritDoc}
     */
    public function parse($path)
    {
        $name = '';
        if ($path) {
            if (false === ($pos = strpos($this->paramDelimiter, $path))) {
                list($name, $path) = explode($this->paramDelimiter, $path, 2);
            } else {
                $name = $path;
                $path = '';
            }
        }
        $matches = parent::parse($path);
        // Set id or name
        if (is_numeric($name)) {
            $matches['id'] = (int) $name;
        } else {
            $matches['name'] = $name;
        }
        // Set action
        if (!empty($matches['type'])) {
            $matches['action'] = $matches['type'];
            unset($matches['type']);
        }

        return $matches;
    }

    /**
     * {@inheritDoc}
     */
    public function assemble(array $params = array(), array $options = array())
    {
        $mergedParams = array_merge($this->defaults, $params);
        $url = '';
        if (!empty($mergedParams['slug'])) {
            $url = $this->encode($mergedParams['slug']);
        } elseif (!empty($mergedParams['name'])) {
            $url = $this->encode($mergedParams['name']);
        } elseif (!empty($mergedParams['id'])) {
            $url = (int) $mergedParams['id'];
        }
        if (empty($url)) {
            return $this->prefix;
        }
        // Keep type
        if (!empty($params['type'])) {
            $params = array('type' => $params['type']);
        } else {
            $params = array();
        }
        $prefix = $this->prefix;
        $this->prefix .= $this->paramDelimiter . $url;
        $url = parent::assemble($params, $options);
        $this->prefix = $prefix;

        return $url;
    }
}
