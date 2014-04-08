<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Route;

use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Stdlib\RequestInterface as Request;
use Pi\Mvc\Router\Http\Standard;
use Pi;

/**
 * Custom default route class, using for SEO
 * 
 * Example:
 * <CODE>
 * $routeName = Pi::api('api', $module)->getRouteName();
 * // Article homepage
 * $this->url($routeName, array('controller' => 'article', 'action' => 'index'));
 * // Article all list page, t and p are extra parameters
 * $this->url($routeName, array('list' => 'all', 't' => 20, 'p' => 2));
 * // Article category list page, p is extra parameter
 * $this->url($routeName, array('category' => 'sport', 'p' => 3));
 * // Tag related article list page
 * $this->url($routeName, array('tag' => 'trip'));
 * // Article detail page, the value of time field is the article published time
 * $this->url($routeName, array('id' => 3, 'time' => '20130725');
 * // Article detail page with slug
 * $this->url($routeName, array('slug' => 'wonderfularticle', 'time' => '20010101')));
 * // Topic homepage
 * $this->url($routeName, array('topic' => 'music'));
 * // Topic article list page
 * $this->url($routeName, array('topic' => 'sodkf', 'list' => 'all', 'from' => 'my'));
 * // Topic title list page
 * $this->url($routeName, array('topic' => 'all'));
 * </CODE>
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Article extends Standard
{
    const URL_DELIMITER       = '?';
    const KEY_VALUE_DELIMITER = '=';
    const COMBINE_DELIMITER   = '&';
    
    protected $paramDelimiter = '-';
    protected $prefix = '/article';
    
    protected $defaults = array(
        'module'     => 'article',
        'controller' => 'index',
        'action'     => 'index',
    );

    /**
     * Match url and resolving parameters
     * 
     * @param Request  $request
     * @param int      $pathOffset
     * @return null|\Zend\Mvc\Router\Http\RouteMatch 
     */
    public function match(Request $request, $pathOffset = null)
    {
        $result = $this->canonizePath($request, $pathOffset);
        if (null === $result) {
            return null;
        }
        list($path, $pathLength) = $result;

        $matches   = array();
        $url       = $path;
        $uri       = $request->getRequestUri();
        $parameter = '';
        if (strpos($uri, self::URL_DELIMITER)) {
            $parameter = substr($uri, strpos($uri, self::URL_DELIMITER) + 1);
        }
        if (empty($url)) {
            $controller = 'article';
            $action     = 'index';
        } else {
            $urlParams = explode($this->structureDelimiter, $url);
            if (preg_match(
                '/^list' . $this->keyValueDelimiter . '/',
                $urlParams[0]
            )) {
                list($ignored, $category) = explode(
                    $this->keyValueDelimiter, 
                    $urlParams[0],
                    2
                );
                if ('all' == $category) {
                    $controller = 'list';
                    $action     = 'all';
                } else {
                    $controller = 'list';
                    $action     = 'all';
                    $category   = $this->decode($category);
                }
                if (preg_match('/^sort-/', $urlParams[1])) {
                    list($ignored, $sort) = explode(
                        $this->keyValueDelimiter, 
                        $urlParams[1],
                        2
                    );
                }
            } elseif (preg_match(
                '/^tag' . $this->keyValueDelimiter . '/',
                $urlParams[0]
            )) {
                list($ignored, $tag) = explode(
                    $this->keyValueDelimiter, 
                    $urlParams[0],
                    2
                );
                $controller = 'tag';
                $action     = 'list';
                $tag        = $this->decode($tag);
            } elseif (preg_match(
                '/^id' . $this->keyValueDelimiter . '/',
                $urlParams[0]
            )) {
                list($ignored, $uniqueVal) = explode(
                    $this->keyValueDelimiter, 
                    $urlParams[0],
                    2
                );
                $id   = is_numeric($uniqueVal) ? $uniqueVal : 0;
                $slug = !is_numeric($uniqueVal) ? $this->decode($uniqueVal) : '';
                $controller = 'article';
                $action     = 'detail';
            } elseif ('topic' == $urlParams[0]) {
                $controller = 'topic';
                $action = 'all-topic';
            } elseif (preg_match(
                '/^topic' . $this->keyValueDelimiter . '/',
                $urlParams[0]
            )) {
                $controller = 'topic';
                list($ignored, $topic) = explode(
                    $this->keyValueDelimiter, 
                    $urlParams[0],
                    2
                );
                if (preg_match(
                    '/^list' . $this->keyValueDelimiter . '/',
                    $urlParams[1]
                )) {
                    $action = 'list';
                } else {
                    $action = 'index';
                }
            } else {
                return null;
            }
        }
        $matches  = compact(
            'controller', 'action', 'category', 'tag', 'id', 'slug', 'topic',
            'sort'
        );
        $matches = array_filter($matches);
        
        $params   = array_filter(explode(self::COMBINE_DELIMITER, $parameter));
        foreach ($params as $param) {
            list($key, $value) = explode(self::KEY_VALUE_DELIMITER, $param);
            if (!isset($matches[$key])) {
                $matches[$key] = $this->decode($value);
            }
        }
        if (isset($matches['preview']) and $matches['preview'] == 1) {
            $matches['controller'] = 'draft';
            $matches['action']     = 'preview';
        }
        
        return new RouteMatch(
            array_merge($this->defaults, $matches), 
            $pathLength
        );
    }

    /**
     * Assemble url by passed parameters.
     * 
     * @param array $params
     * @param array $options
     * @return string 
     */
    public function assemble(array $params = array(), array $options = array())
    {
        $url = '';

        $mergedParams = array_merge($this->defaults, $params);
        if (empty($mergedParams)) {
            return $this->prefix;
        }
        
        $controller = $mergedParams['controller'];
        $action     = $mergedParams['action'];
        if ('article' == $controller and 'index' == $action) {
            return $this->prefix;
        }
        unset($mergedParams['controller']);
        unset($mergedParams['action']);
        unset($mergedParams['module']);
        
        if (isset($mergedParams['slug'])
            && !empty($mergedParams['slug'])
            && !is_numeric($mergedParams['slug'])
        ) {
            $url .= 'id'
                 . $this->keyValueDelimiter 
                 . $this->encode($mergedParams['slug']);
            if (!isset($mergedParams['preview'])) {
                unset($mergedParams['id']);
            }
            unset($mergedParams['slug']);
            unset($mergedParams['time']);
        } elseif (isset($mergedParams['id'])
            && !empty($mergedParams['id'])
            && is_numeric($mergedParams['id'])
        ) {
            $url .= 'id'
                 . $this->keyValueDelimiter 
                 . $mergedParams['id'];
            if (!isset($mergedParams['preview'])) {
                unset($mergedParams['id']);
            }
            unset($mergedParams['slug']);
            unset($mergedParams['time']);
        } elseif (isset($mergedParams['topic'])) {
            if ('all' == $mergedParams['topic']) {
                $url .= 'topic';
            } elseif (isset($mergedParams['list'])) {
                $url .= 'topic' . $this->keyValueDelimiter
                     . $mergedParams['topic']
                     . $this->structureDelimiter
                     . 'list' . $this->keyValueDelimiter
                     . $mergedParams['list'];
                unset($mergedParams['list']);
            } else {
                $url .= 'topic' . $this->keyValueDelimiter
                     . $mergedParams['topic'];
            }
            unset($mergedParams['topic']);
        } elseif (isset($mergedParams['list'])) {
            $url .= 'list'
                 . $this->keyValueDelimiter 
                 . $this->encode($mergedParams['list']);
            unset($mergedParams['list']);
        } elseif (isset($mergedParams['category'])
            || 'list' == $controller
        ) {
            $mergedParams['category'] = $mergedParams['category'] ?: 'all';
            $url .= 'list' 
                 . $this->keyValueDelimiter 
                 . $this->encode($mergedParams['category']);
            unset($mergedParams['category']);
            if (isset($mergedParams['sort'])) {
                $url .= $this->structureDelimiter
                     . 'sort'
                     . $this->keyValueDelimiter
                     . $mergedParams['sort'];
                unset($mergedParams['sort']);
            }
        } elseif (isset($mergedParams['tag'])) {
            $url .= 'tag' 
                 . $this->keyValueDelimiter 
                 . $this->encode($mergedParams['tag']);
            unset($mergedParams['tag']);
        }
        
        $parameter = '';
        $mergedParams = array_filter($mergedParams);
        if (!empty($mergedParams)) {
            foreach ($mergedParams as $key => $value) {
                $parameter .= $key 
                           . self::KEY_VALUE_DELIMITER 
                           . $this->encode($value)
                           . self::COMBINE_DELIMITER;
            }
            $parameter = rtrim($parameter, self::COMBINE_DELIMITER);
            $url .= self::URL_DELIMITER . $parameter;
        }

        return $this->prefix 
            . $this->structureDelimiter 
            . trim($url, $this->structureDelimiter);
    }
}
