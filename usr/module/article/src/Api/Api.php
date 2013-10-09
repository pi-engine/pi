<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

namespace Module\Article\Api;

use Pi\Application\AbstractApi;
use Module\Article\Model\Article;
use Pi;

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
        return Pi::engine()->application()
            ->getRouter()
            ->assemble(array(
                'module'     => $this->getModule(),
                'controller' => 'draft',
                'action'     => 'add',
            ), array('name' => 'default'));
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
            $user      = Pi::service('user')->getUser();
            $submitter = Pi::user()->id;
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
        
        // Get statistics data
        $staticSet = Pi::model('statistics', $module)->getList(array(
            'article' => $articleIds,
        ));
        
        foreach ($resultSet as &$row) {
            $statistics = isset($staticSet[$row['id']]) 
                ? $staticSet[$row['id']] : array();
            unset($statistics['article']);
            unset($statistics['id']);
            $row = array_merge($row, $statistics);
        }
        
        return $resultSet;
    }
}