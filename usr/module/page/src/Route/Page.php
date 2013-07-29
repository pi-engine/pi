<?php
/**
 * Page route implementation
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
 * @package         Module\Page
 * @subpackage      Route
 * @version         $Id$
 */

namespace Module\Page\Route;

use Pi\Mvc\Router\Http\Standard;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Stdlib\RequestInterface as Request;

/**
 * Route for pages
 *
 *  1. ID: /url/page/view/123
 *  2. Slug: /url/page/view/my-slug
 */
class Page extends Standard
{
    protected $prefix = '/page';

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

        /* list($path1, $path2) = explode($this->paramDelimiter, $path, 2);
        
        if(empty($path2)) {
	        if(is_numeric($path1)) {
	            $matches['id'] = intval($path1);	
	        } else {
	            $matches['slug'] = urldecode($path1);
	        }
        } else {
	        $matches['action'] = $path1;
	        if(is_numeric($path2)) {
	            $matches['id'] = intval($path2);	
	        } else {
	            $matches['slug'] = urldecode($path2);
	        }
        } */
        
        	list($url) = explode($this->paramDelimiter, $path, 1);
        	
        	if(is_numeric($url)) {
	            $matches['id'] = intval($url);
	        } else {
	            $matches['action'] = $matches['slug'] = urldecode($url);
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
        
        // Set path
        if (!empty($mergedParams['slug'])) {
            $url = urlencode($mergedParams['slug']);
            //$action = '';
        } else {
            $url = intval($mergedParams['id']);
            //$action = '';
        }
        
        // Set action
        /* if (!empty($mergedParams['name'])) {
            $action = urlencode($mergedParams['name']);
        } elseif (!empty($mergedParams['action']) && $mergedParams['action'] != 'index') {
            $action = urlencode($mergedParams['action']);
        } */

        if (empty($url)/* && empty($action)*/) {
            return $this->prefix;
        }
        
        /* if(!empty($action)) {
            $url = $action . $this->paramDelimiter . $url;	
        } */
        
        return $this->paramDelimiter . trim($this->prefix, $this->paramDelimiter) . $this->paramDelimiter . $url;
    }
}
