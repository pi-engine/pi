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
use Module\Article\Entity;

/**
 * Topic service class
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Topic
{
    protected static $module = 'article';
    
    /**
     * Get topic id by passed topic name
     * 
     * @param string  $name    Topic unique name
     * @param string  $module
     * @return int 
     */
    public static function getTopicId($name, $module = null)
    {
        $module = $module ?: Pi::service('module')->current();
        $topic  = Pi::model('topic', $module)->find($name, 'name');
        
        return $topic->id;
    }

    /**
     * Get articles belonging to a certain topic by passed condition
     * 
     * @param array   $where
     * @param int     $page
     * @param int     $limit
     * @param array   $columns
     * @param string  $order
     * @param string  $module
     * @return array 
     */
    public static function getTopicArticles(
        $where,
        $page,
        $limit,
        $columns = null,
        $order = null,
        $module = null
    ) {
        $module     = $module ?: Pi::service('module')->current();
        $topicWhere = array();
        if (!empty($where['topic'])) {
            $topicId = is_numeric($where['topic']) 
                ? $where['topic'] 
                : self::getTopicId($where['topic'], $module);
            $topicWhere['topic'] = $topicId;
            unset($where['topic']);
        }
        
        $modelTopic = Pi::model('article_topic', $module);
        $rowTopic   = $modelTopic->select($topicWhere);
        $articleIds = array();
        foreach ($rowTopic as $row) {
            $articleIds[] = $row['article'];
        }
        $where['id'] = $articleIds;
        
        return Entity::getAvailableArticlePage(
            $where,
            $page,
            $limit,
            $columns,
            $order,
            $module
        );
    }
    
    /**
     * Get topic details by passed condition
     * 
     * @param array   $where
     * @param int     $page
     * @param int     $limit
     * @param array   $columns
     * @param string  $order
     * @param string  $module
     * @return array 
     */
    public static function getTopics(
        $where,
        $page,
        $limit,
        $columns = null,
        $order = null,
        $module = null
    ) {
        $offset     = ($limit && $page) ? $limit * ($page - 1) : null;
        $where      = empty($where) ? array() : (array) $where;
        $columns    = empty($columns) ? array('*') : $columns;
        $module     = $module ?: Pi::service('module')->current();
        
        $modelTopic = Pi::model('topic', $module);
        $select     = $modelTopic->select()->where($where)->columns($columns);
        if (!empty($page)) {
            $select->offset($offset);
        }
        if (!empty($limit)) {
            $select->limit($limit);
        }
        if (!empty($order)) {
            $select->order($order);
        }
        $rowset = $modelTopic->selectWith($select);
        $topics = array();
        foreach ($rowset as $row) {
            $id   = $row->id;
            $topics[$id] = $row->toArray();
            $topics[$id]['url'] = Pi::api('api', $module)->getUrl(
                'topic-home',
                array('topic'     => $row->slug ?: $row->id),
                $row->toArray()
            );
        }
        
        return $topics;
    }
    
    /**
     * Get top visits in period of topic articles
     * 
     * @param int     $days
     * @param int     $limit
     * @param int     $category
     * @param int     $topic
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
        $modelTopic   = Pi::model('article_topic', $module);
        
        if (null === $columns) {
            $columns = $modelArticle->getColumns(true, true);
        }
        if (!in_array('id', $columns)) {
            $columns[] = 'id';
        }
        
        $limit = (int) $limit ?: 10;
        
        $topic  = isset($where['topic']) ? $where['topic'] : '';
        unset($where['topic']);
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
        
        if ($topic) {
            $newWhere['t.topic'] = $topic;
        } else {
            $newWhere['t.topic > ?'] = 0;
        }
        
        // Start time condition
        $startTime = Entity::getStartTime($range);
        $newWhere['s.time_updated > ?'] = $startTime;
        
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
            )->join(
                array('t' => $modelTopic->getTable()),
                sprintf('%s.id = t.article', $prefix),
                array('topic' => 'topic'),
                'left'
            )->where($newWhere)
            ->group(sprintf('%s.id', $prefix))
            ->order('s.visits DESC')
            ->limit($limit);
        if ($offset) {
            $select->offset($offset);
        }
        $rowset = $modelArticle->selectWith($select)->toArray();
        
        $result = Entity::canonize($rowset, $module);
        
        return $result;
    }
}
