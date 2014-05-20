<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Custom\Article\Route;

use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Stdlib\RequestInterface as Request;
use Pi\Mvc\Router\Http\Standard;
use Pi;

/**
 * Custom default route class, using for SEO
 * 
 * Example:
 * <CODE>
 * $routeName = Service::getRouteName();
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
            if ('list' == $urlParams[0]) {
                $controller = 'list';
                $action     = 'all';
            } elseif (preg_match('/^list-/', $urlParams[0])) {
                list($ignored, $category) = explode(
                    $this->keyValueDelimiter, 
                    $urlParams[0],
                    2
                );
                $controller = 'category';
                $action     = 'list';
                $category   = $this->decode($category);
                if (preg_match('/^sort-/', $urlParams[1])) {
                    list($ignored, $sort) = explode(
                        $this->keyValueDelimiter, 
                        $urlParams[1],
                        2
                    );
                }
            } elseif (preg_match('/^tag-/', $urlParams[0])) {
                list($ignored, $tag) = explode(
                    $this->keyValueDelimiter, 
                    $urlParams[0],
                    2
                );
                $tag        = $this->decode($tag);
                $controller = 'tag';
                $action     = 'list';
            } elseif (preg_match('/\d{6}/', $urlParams[0])) {
                $controller = 'article';
                $action     = 'detail';
                if (is_numeric($urlParams[1])) {
                    $id     = $urlParams[1];
                } elseif (is_string($urlParams[1])) {
                    $slug   = $this->decode($urlParams[1]);
                } else {
                    return null;
                }
            } elseif ('topic' == $urlParams[0]) {
                $controller = 'topic';
                if (!isset($urlParams[1])) {
                    $action = 'all-topic';
                } elseif (preg_match('/^list-/', $urlParams[1])) {
                    list($ignored, $topic) = explode(
                        $this->keyValueDelimiter, 
                        $urlParams[1],
                        2
                    );
                    $action = 'list';
                } else {
                    $topic = $urlParams[1];
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
        
        if (isset($mergedParams['time']) 
            and is_numeric($mergedParams['time'])
        ) {
            if (isset($mergedParams['slug']) 
                and !empty($mergedParams['slug']) 
                and !is_numeric($mergedParams['slug'])
            ) {
                $url .= $mergedParams['time'] 
                     . $this->structureDelimiter 
                     . $this->encode($mergedParams['slug']);
                unset($mergedParams['slug']);
                if (!isset($mergedParams['preview'])) {
                    unset($mergedParams['id']);
                }
            } elseif (isset($mergedParams['id']) 
                      and !empty($mergedParams['id']) 
                      and is_numeric($mergedParams['id'])
            ) {
                $url .= $mergedParams['time'] 
                     . $this->structureDelimiter 
                     . $mergedParams['id'];
                if (!isset($mergedParams['preview'])) {
                    unset($mergedParams['id']);
                }
            }
            unset($mergedParams['time']);
        } elseif (isset($mergedParams['list']) 
                  and 'all' == $mergedParams['list']
        ) {
            if (isset ($mergedParams['topic'])) {
                $url .= 'topic';
                $url .= $this->structureDelimiter . 'list';
                $url .= $this->keyValueDelimiter . $mergedParams['topic'];
                unset($mergedParams['topic']);
            } else {
                $url .= 'list';
            }
            unset($mergedParams['list']);
        } elseif (isset($mergedParams['category'])) {
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
        } elseif (isset($mergedParams['topic'])) {
            $url .= 'topic';
            if ('all' == $mergedParams['topic']) {
                $url .= '';
            } else {
                $url .= $this->structureDelimiter . $mergedParams['topic'];
            }
            unset($mergedParams['topic']);
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
