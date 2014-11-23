<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Api;

use Pi;
use Pi\Application\Api\AbstractApi;

/**
 * Public API for other module
 * 
 * @author Zongshu Lin <lin40553024@163.com> 
 */
class Api extends AbstractApi
{
    protected $module = 'article';

    /**
     * Get page URL
     * 
     * @param string  $type     Which page URL want to get
     * @param array   $params   Parameters for assembling URL
     * @param array   $options  Additional parameters for assembling URL
     * @return string
     */
    public function getUrl($type, $params = array(), $options = array())
    {
        // Get custom URL
        $module = $this->getModule();
        $params['module'] = $module;
        $class  = sprintf('Custom\%s\Api\Api', ucfirst($module));
        if (class_exists($class)) {
            $handler = new $class($module);
            return $handler->getUrl($type, $params, $options);
        }
        
        // Default process
        switch($type) {
            case 'home':
                $params['controller'] = 'article';
                $params['action']     = 'index';
                break;
            case 'list':
                $params['controller'] = 'list';
                $params['action']     = 'index';
                // Optional parameters
                //$category = Pi::api('category', $module)->get($options['category']);
                //$cluster  = Pi::api('cluster', $module)->get($options['cluster']);
                break;
            case 'detail':
                $params['controller'] = 'article';
                $params['action']     = 'detail';
                break;
            case 'topic-home':
                $params['controller'] = 'topic';
                $params['action']     = 'index';
                break;
            case 'topic-list':
                $params['controller'] = 'topic';
                $params['action']     = 'list';
                $params['list']       = 'all';
                break;
            case 'topics':
                $params['controller'] = 'topic';
                $params['action']     = 'all-topic';
                $params['topic']      = 'all';
                break;
            case 'tag-list':
                $params['controller'] = 'tag';
                $params['action']     = 'list';
                break;
            case 'category-home':
                $params['controller'] = 'category';
                $params['action']     = 'index';
                break;
            case 'cluster-home':
                $params['controller'] = 'cluster';
                $params['action']     = 'index';
                break;
        }
        $route = $this->getRouteName();
        $url   = Pi::service('url')->assemble($route, $params);
        
        return $url;
    }
    
    /**
     * Get compose url
     * 
     * @return string 
     */
    public function getComposeUrl()
    {
        return Pi::service('url')->assemble('default', array(
            'module'     => $this->getModule(),
            'controller' => 'draft',
            'action'     => 'add',
        ));
    }
    
    /**
     * Get published article list of a submitter
     * 
     * @param int|null  $submitter
     * @param int|null  $page
     * @param int|null  $limit
     * @return array 
     */
    public function getListBySubmitter(
        $submitter = null, 
        $page = null, 
        $limit = null
    ) {
        if (empty($submitter)) {
            $submitter = Pi::user()->getId();
        }
        if (!is_numeric($submitter)) {
            return array();
        }
        
        if (!empty($limit)) {
            $page = $page ?: 1;
        }
        $order = 'time_publish DESC';
        
        $where = array(
            'uid'  => $submitter,
        );
        
        if (!empty($page) and empty($limit)) {
            $offset = ((int) $page - 1) * (int) $limit;
        }
        
        // Get article result set
        $module    = $this->getModule();
        $resultSet = Pi::model('article', $module)->getSearchRows(
            $where, 
            $limit, 
            isset($offset) ? $offset : null, 
            null,
            $order
        );
        
        if (empty($resultSet)) {
            return array();
        }
        
        // Get article ID
        $articleIds = array();
        foreach ($resultSet as $set) {
            $articleIds[] = $set['id'];
        }
        
        // Get stats data
        $staticSet = Pi::model('stats', $module)->getList(array(
            'article' => $articleIds,
        ));
        
        foreach ($resultSet as &$row) {
            $stats = isset($staticSet[$row['id']]) 
                ? $staticSet[$row['id']] : array();
            unset($stats['article']);
            unset($stats['id']);
            $row = array_merge($row, $stats);
        }
        
        return $resultSet;
    }
    
    /**
     * Read author data from cache by ID
     * 
     * @param array  $ids
     * @return array 
     */
    public function getAuthorList($where = array(), $columns = null)
    {
        $module = $this->getModule();
        $rows   = Pi::registry('author', $module)->read($module);
        
        $result = $this->filterData($rows, $where, $columns);
        
        return $result;
    }

    /**
     * Get route name
     *
     * @param string $module
     * @return string
     */
    public function getRouteName($module = '')
    {
        return 'article';
    }
    
    /**
     * Filter data by where condition and allowed columns
     * 
     * @param array $data
     * @param array $where
     * @param array $columns
     * @return array
     */
    public function filterData($data, $where = array(), $columns = null)
    {
        foreach ($data as $id => &$row) {
            if (!empty($where)) {
                foreach ($where as $key => $val) {
                    if (false === strpos($key, '?')) {
                        if ((!is_array($val)
                            && strtolower($row[$key]) != strtolower($val))
                            || (is_array($val)
                            && !in_array(strtolower($row[$key]), $val))) {
                            unset($data[$id]);
                            break;
                        }
                    } else {
                        if (!is_array($val)) {
                            list($key, $symbol) = explode(' ', $key);
                            $val      = strtolower($val);
                            $cacheVal = strtolower($row[$key]);
                            switch ($symbol) {
                                case '>=':
                                    $result = ($cacheVal >= $val);
                                    break;
                                case '>':
                                    $result = ($cacheVal > $val);
                                    break;
                                case '<=':
                                    $result = ($cacheVal <= $val);
                                    break;
                                case '<':
                                    $result = ($cacheVal < $val);
                                    break;
                                case '!=':
                                case '<>':
                                    $result = ($cacheVal != $val);
                                    break;
                            }
                            if (!$result) {
                                unset($data[$id]);
                                break;
                            }
                        }
                    }
                }
            }
            if (null !== $columns) {
                foreach (array_keys($row) as $key) {
                    if (!in_array($key, $columns)) {
                        unset($row[$key]);
                    }
                };
            }
        }
        
        return $data;
    }
    
    /**
     * Custom order for different pages
     * 
     * @param array $options  Page info
     * <code>
     * 'section': 'front', 'admin' or 'block'
     * 'controller'
     * 'action'
     * </code>
     * @return string
     */
    public function canonizeOrder($options = array())
    {
        // Get custom URL
        $module = $this->getModule();
        $class  = sprintf('Custom\%s\Api\Api', ucfirst($module));
        $method = substr(__METHOD__, strpos(__METHOD__, '::') + 2);
        if (class_exists($class) && method_exists($class, $method)) {
            $handler = new $class($module);
            return $handler->canonizeOrder($options);
        }
        
        return '';
    }
    
    /**
     * Custom columns to fetch for different pages
     * 
     * @param array $options  Page info
     * <code>
     * 'section': 'front', 'admin' or 'block'
     * 'controller'
     * 'action'
     * </code>
     * @return string
     */
    public function canonizeColumns($options = array())
    {
        // Get custom URL
        $module = $this->getModule();
        $class  = sprintf('Custom\%s\Api\Api', ucfirst($module));
        $method = substr(__METHOD__, strpos(__METHOD__, '::') + 2);
        if (class_exists($class) && method_exists($class, $method)) {
            $handler = new $class($module);
            return $handler->canonizeColumns($options);
        }
        
        return array(
            'id', 'subject', 'summary', 'time_publish', 'category', 
            'cluster', 'uid', 'author', 'active', 'image'
        );
    }
}
