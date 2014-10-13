<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Article;

use Pi;
use Zend\Db\Sql\Expression;
use Module\Article\Model\Article;
use Module\Article\Model\Draft as DraftModel;

/**
 * Article service APIs
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Entity
{
    protected static $module = 'article';

    /**
     * Get recently mostly visited articles
     * 
     * @param int     $dateFrom
     * @param int     $dateTo
     * @param int     $limit
     * @param int     $category
     * @param string  $module
     * @return array 
     */
    public static function getTopVisitArticles(
        $range,
        $where = array(),
        $columns = null,
        $offset = null,
        $limit = null,
        $module = null
    ) {
        $module = $module ?: Pi::service('module')->current();

        $modelArticle = Pi::model('article', $module);
        $modelCluster = Pi::model('cluster_article', $module);
        $modelStats   = Pi::model('stats', $module);
        
        if (null === $columns) {
            $columns = $modelArticle->getColumns(true, true);
        }
        if (!in_array('id', $columns)) {
            $columns[] = 'id';
        }
        
        $limit = (int) $limit ?: 10;
        
        $prefix = $modelArticle->getTable();
        $newWhere = array();
        foreach ($where as $key => $val) {
            if ('cluster' === $key) {
                $newWhere['c.cluster'] = $val;
                continue;
            }
            $newKey = sprintf('%s.%s', $prefix, $key);
            $newWhere[$newKey] = $val;
        }
        $newWhere['s.date'] = $range;
        
        // Start time condition
        $startTime = self::getStartTime($range);
        $newWhere['s.time_updated >= ?'] = $startTime;
        
        $select = $modelArticle->select()
            ->columns($columns)
            ->join(
                array('c' => $modelCluster->getTable()),
                sprintf('%s.id = c.article', $prefix),
                array('clusters' => 'cluster'),
                'left'
            )->join(
                array('s' => $modelStats->getTable()),
                sprintf('%s.id = s.article', $prefix),
                array('total' => 'visits'),
                'left'
            )->where($newWhere)
            ->group(sprintf('%s.id', $prefix))
            ->order('s.visits DESC')
            ->limit($limit);
        if ($offset) {
            $select->offset($offset);
        }
        $rowset = $modelArticle->selectWith($select)->toArray();
        
        $result = self::canonize($rowset, $module);

        return $result;
    }

    /**
     * Get published article details
     * 
     * @param array   $where
     * @param int     $page
     * @param int     $limit
     * @param array   $columns
     * @param string  $order
     * @param string  $module
     * @return array 
     */
    public static function getArticlePage(
        $where, 
        $page, 
        $limit, 
        $columns = null, 
        $order = null, 
        $module = null
    ) {
        $offset = ($limit && $page) ? $limit * ($page - 1) : null;

        $module = $module ?: Pi::service('module')->current();

        $modelArticle = Pi::model('article', $module);
        $modelCluster = Pi::model('cluster_article', $module);
        
        if (null === $columns) {
            $columns = $modelArticle->getColumns(true, true);
        }
        if (!in_array('id', $columns)) {
            $columns[] = 'id';
        }
        
        $order = (null === $order) ? 'time_publish DESC' : $order;
        
        $prefix = $modelArticle->getTable();
        $newWhere = array();
        foreach ($where as $key => $val) {
            if ('cluster' === $key) {
                $newWhere['c.cluster'] = $val;
                continue;
            }
            $newKey = sprintf('%s.%s', $prefix, $key);
            $newWhere[$newKey] = $val;
        }
        
        $select = $modelArticle->select()
            ->columns($columns)
            ->join(
                array('c' => $modelCluster->getTable()),
                sprintf('%s.id = c.article', $prefix),
                array('clusters' => 'cluster'),
                'left'
            )->where($newWhere)
            ->group(sprintf('%s.id', $prefix))->order($order);
        if ($limit) {
            $select->limit(intval($limit));
        }
        if ($offset) {
            $select->offset(intval($offset));
        }
        $rowset = $modelArticle->selectWith($select)->toArray();

        $result = self::canonize($rowset, $module);

        return $result;
    }
    
    /**
     * Resolve articles info into readable info
     * 
     * @param array $data
     * @return array
     */
    public static function canonize(array $data, $module = null)
    {
        $result = array();
        
        if (!count($data)) {
            return $result;
        }
        
        $ids = $uids = array();
        foreach ($data as $row) {
            $ids[$row['id']] = $row['id'];
            if (!empty($row['uid'])) {
                $uids[$row['uid']] = $row['uid'];
            }
            $result[$row['id']] = $row;
        }

        $module = $module ?: Pi::service('module')->current();
        
        // Get clusters
        $clusters = Pi::api('cluster', $module)->getArticleClusters($ids);

        $users = array();
        if (!empty($uids)) {
            $users = Pi::user()->get($uids, array('id', 'name'));
        }

        foreach ($result as &$row) {
            $row = Pi::api('field', $module)->resolver($row);

            if (!empty($uids) && isset($users[$row['uid']])) {
                $row['user'] = $users[$row['uid']];
            }
            if (isset($clusters[$row['id']])) {
                $row['clusters'] = $clusters[$row['id']];
            }
            
            $params = array(
                'time' => date('Ymd', $row['time_publish']),
                'id'   => $row['id'],
            );
            $row['url'] = Pi::api('api', $module)->getUrl('detail', $params, $row);
        }
        
        return $result;
    }
    
    /**
     * Get start time of today, this week or this month
     * 
     * @param string  $date  'day', 'week', 'month' or 'all'
     * @return int
     */
    public static function getStartTime($date)
    {
        $today = time();
        $startTime  = 0;
        switch ($date) {
            case 'D':
                $date      = date('Y-m-d', $today);
                $startTime = strtotime($date);
                break;
            case 'W':
                $todayDate = date('Y-m-d', $today);
                $weekDay   = date('w', $today);
                $startTime = strtotime($todayDate) - $weekDay * 24 * 3600;
                break;
            case 'M':
                $date      = date('Y-m-01', $today);
                $startTime = strtotime($date);
                break;
            case 'A':
                break;
            default:
                
        }
        
        return $startTime;
    }

    /**
     * Get available articles which are published and active
     * 
     * @param array   $where
     * @param int     $page
     * @param int     $limit
     * @param array   $columns
     * @param string  $order
     * @param string  $module
     * @return array 
     */
    public static function getAvailableArticlePage(
        $where, 
        $page, 
        $limit, 
        $columns = null, 
        $order = null,
        $module = null
    ) {
        $defaultWhere = array(
            'time_publish <= ?' => time(),
            'status'            => Article::FIELD_STATUS_PUBLISHED,
            'active'            => 1,
        );
        $where = $where ? array_merge($where, $defaultWhere) : $defaultWhere;

        return self::getArticlePage($where, $page, $limit, $columns, $order, $module);
    }

    /**
     * Get published article details
     * 
     * @param int  $id  Article ID
     * @return array 
     */
    public static function getEntity($id)
    {
        $module = Pi::service('module')->current();

        $row = Pi::model('article', $module)->find($id);
        if (empty($row->id)) {
            return array();
        }
        $result = Pi::api('field', $module)->resolver($row->toArray());

        // Get compound data
        $compound = Pi::registry('field', $module)->read('compound');
        foreach (array_keys($compound) as $name) {
            $handler = Pi::api('field', $module)->loadCompoundFieldHandler($name);
            $data    = $handler->encode($id);
            $result[$name] = $handler->resolve(array_pop($data));
        }
        
        // Get custom data
        $custom = Pi::registry('field', $module)->read('custom');
        foreach (array_keys($custom) as $name) {
            $handler = Pi::api('field', $module)->loadCustomFieldHandler($name);
            $data    = $handler->encode($id);
            $result[$name] = $handler->resolve(array_pop($data));
        }
        
        // Get page URL
        foreach ($result['content'] as &$value) {
            $params = array(
                'time' => date('Ymd', $result['time_publish']),
                'id'   => $id,
                'p'    => $value['page'],
            );
            $value['url'] = Pi::api('api', $module)->getUrl('detail', $params, $result);
        }
        
        // Getting stats data
        $result['visits'] = Stats::getTotalVisit($id, 'A');

        return $result;
    }

    /**
     * Get count statistics of draft with different status and published article
     *
     * @param string $from
     * @param array  $rules
     *
     * @return array
     */
    public static function getSummary($from = 'my', $rules = array())
    {
        // Processing user management category
        $categories = array_keys($rules);
                    
        $module = Pi::service('module')->current();
        
        $result = array(
            'published' => 0,
            'draft'     => 0,
            'pending'   => 0,
            'rejected'  => 0,
        );

        $where['article < ?'] = 1;
        if ('my' == $from) {
            $where['uid'] = Pi::user()->getId() ?: 0;
        }
        $modelDraft = Pi::model('draft', $module);
        $select     = $modelDraft->select()
            ->columns(array(
                'status', 
                'total' => new Expression('count(status)'), 
                'category'
            ))
            ->where($where)
            ->group(array('status', 'category'));
        $resultSet  = $modelDraft->selectWith($select);
        foreach ($resultSet as $row) {
            if (DraftModel::FIELD_STATUS_DRAFT == $row->status) {
                $result['draft'] += $row->total;
            } else if (DraftModel::FIELD_STATUS_PENDING == $row->status) {
                if ('all' == $from 
                    and in_array($row->category, $categories)
                ) {
                    $result['pending'] += $row->total;
                } elseif ('my' == $from) {
                    $result['pending'] += $row->total;
                }
            } else if (DraftModel::FIELD_STATUS_REJECTED == $row->status) {
                $result['rejected'] += $row->total;
            }
        }

        $modelArticle = Pi::model('article', $module);
        $where        = array(
            'status'   => Article::FIELD_STATUS_PUBLISHED,
            'category' => !empty($categories) ? $categories : 0,
        );
        if ('my' == $from) {
            $where['uid'] = Pi::user()->getId() ?: 0;
        }
        $count = $modelArticle->count($where);
        if ($count) {
            $result['published'] = $count;
        }

        return $result;
    }
    
    /**
     * Get total article number by condition
     * 
     * @param array $where
     * @return int
     */
    public static function count($where)
    {
        $module = Pi::service('module')->current();
        
        $modelArticle = Pi::model('article', $module);
        $modelCluster = Pi::model('cluster_article', $module);
        
        $prefix = $modelArticle->getTable();
        $newWhere = array();
        foreach ($where as $key => $val) {
            if ('cluster' === $key) {
                $newWhere['c.cluster'] = $val;
                continue;
            }
            $newKey = sprintf('%s.%s', $prefix, $key);
            $newWhere[$newKey] = $val;
        }
        
        $select = $modelArticle->select()
            ->columns(array('count' => new Expression("count(distinct $prefix.id)")))
            ->join(
                array('c' => $modelCluster->getTable()),
                sprintf('%s.id = c.article', $prefix),
                array(),
                'left'
            )->where($newWhere);
        $count = $modelArticle->selectWith($select)->current()->count;
        
        return (int) $count;
    }
}
