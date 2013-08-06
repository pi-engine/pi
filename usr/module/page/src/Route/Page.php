<?php
use Zend\EventManager\Event;

namespace Module\Page\Route;

use Pi\Mvc\Router\Http\Standard;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Stdlib\RequestInterface as Request;

/**
 * Route for pages
 *
 *  1. ID: /url/page/view/123
 *  2. Slug: /url/page/view/my-slug
 *  3. Name via action: /url/page/name
 */
class Page extends Standard
{
    //protected $prefix = '/page';

    /**
     * Default values.
     *
     * @var array
     */
    protected $defaults = array(
        'module'        => 'page',
        'controller'    => 'index',
        'action'        => 'index'
    );

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
        if (empty($path)) {
            return null;
        }

        $action = null;
        $id = null;
        $slug = null;
        $name = null;
        // Name via action: /url/page/name
        if (false === strpos($path, $this->paramDelimiter)) {
            if (preg_match('/[a-z0-9_]/', $path)) {
                $action = $path;
                //$name = $path;
            } else {
                return null;
            }
        // ID/Slug via: /url/page/view/id, /url/page/view/slug
        // Action with ID/Slug via: /url/page/name/id, /url/page/name/slug
        } else {
            list($view, $param) = explode($this->paramDelimiter, $path, 2);
            if ('view' != $view) {
                $action = $view;
            }
            if (is_numeric($param)) {
                $id = $slug;
            } else {
                $slug = $param;
            }
        }

        $matches = array(
            'name'          => $name,
            'id'            => $id ? intval($id) : null,
            'slug'          => $slug ? urldecode($slug) : null,
        );
        if ($action) {
            $matches['action'] = $action;
        }

        return new RouteMatch(array_merge($this->defaults, $matches),
                              $pathLength);
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
        $url = '';
        if (!empty($mergedParams['slug'])) {
            $url = urlencode($mergedParams['slug']);
        } elseif (!empty($mergedParams['id'])) {
            $url = intval($mergedParams['id']);
        }
        $action = '';
        if (!empty($mergedParams['name'])) {
            $action = $mergedParams['name'];
        } elseif (!empty($mergedParams['action'])) {
            $action = $mergedParams['action'];
        }
        if (empty($url) && empty($action)) {
            return $this->prefix;
        }
        if (empty($url)) {
            $url = $action;
        } elseif (!empty($action)) {
            if ($action != 'index') {
                $url = $action . $this->paramDelimiter . $url;
            }
        } else {
            $url = 'view' . $this->paramDelimiter . $url;
        }

        return $this->paramDelimiter
            . trim($this->prefix, $this->paramDelimiter)
            . $this->paramDelimiter . $url;
    }
}
