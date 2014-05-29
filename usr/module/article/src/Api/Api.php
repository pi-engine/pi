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
use Module\Article\Model\Article;
use Module\Article\Installer\Resource\Route;

/**
 * Public API for other module
 * 
 * @author Zongshu Lin <lin40553024@163.com> 
 */
class Api extends AbstractApi
{
    protected $module = 'article';

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
            //$user      = Pi::service('user')->getUser();
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
            Article::getDefaultColumns(), 
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
     * Read category data from cache
     * 
     * @param array $where
     * @return array 
     */
    public function getCategoryList($where = array())
    {
        $isTree = false;
        if (isset($where['is-tree'])) {
            $isTree = $where['is-tree'];
            unset($where['is-tree']);
        }
        $module = $this->getModule();
        $rows   = Pi::service('registry')
            ->handler('category', $module)
            ->read($where, $isTree, $module);
        
        return $rows;
    }
    
    /**
     * Read author data from cache by ID
     * 
     * @param array  $ids
     * @return array 
     */
    public function getAuthorList($ids = array())
    {
        $module = $this->getModule();
        $rows   = Pi::service('registry')
            ->handler('author', $module)
            ->read($module);
        
        if (!empty($ids)) {
            foreach ($rows as $key => $row) {
                if (!in_array($row['id'], $ids)) {
                    unset($rows[$key]);
                }
            }
        }
        
        return $rows;
    }

    /**
     * Get route name
     *
     * @param string $module
     *
     * @return string
     */
    public function getRouteName($module = '')
    {
        return 'article';
        $module = $module ?: $this->getModule();
        $route = $module . '-article';

        return $route;

        /*
        $defaultRoute = $module . '-article';
        $resFilename = sprintf(
            '%s/module/%s/config/route.php',
            Pi::path('custom'),
            $module
        );
        $resPath     = Pi::path($resFilename);
        if (!file_exists($resPath)) {
            return $defaultRoute;
        }
        
        $configs = include $resPath;
        $class   = '';
        $name    = '';
        foreach ($configs as $key => $config) {
            $class = $config['type'];
            $name  = $key;
            break;
        }
        
        if (!class_exists($class)) {
            return $defaultRoute;
        }
        
        // Check if the route is already in database
        $routeName = $module . '-' . $name;
        $cacheName = Pi::service('registry')
            ->handler('route', $module)
            ->read($module);
        if ($routeName != $cacheName) {
            return $defaultRoute;
        }
        
        return $cacheName;
        */
    }
}