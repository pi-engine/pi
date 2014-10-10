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
use Module\Article\Model\Article as ModelArticle;

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
                        if ((!is_array($val) && $row[$key] != $val)
                            || (is_array($val) && !in_array($row[$key], $val))) {
                            unset($data[$id]);
                            break;
                        }
                    } else {
                        if (!is_array($val)) {
                            list($key, $symbol) = explode(' ', $key);
                            switch ($symbol) {
                                case '>=':
                                    $result = ($row[$key] >= $val);
                                    break;
                                case '>':
                                    $result = ($row[$key] > $val);
                                    break;
                                case '<=':
                                    $result = ($row[$key] <= $val);
                                    break;
                                case '<':
                                    $result = ($row[$key] < $val);
                                    break;
                                case '!=':
                                case '<>':
                                    $result = ($row[$key] != $val);
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
}
