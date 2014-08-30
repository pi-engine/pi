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
use Module\Article\Service;
use Module\Article\Entity;
use Module\Article\Model\Article;
use Zend\Db\Sql\Expression;

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
        //$route  = Pi::api('api', $module)->getRouteName();
        foreach ($rowset as $row) {
            $id   = $row->id;
            $topics[$id] = $row->toArray();
            $topics[$id]['url'] = Pi::service('url')->assemble('article', array(
                'module'    => $module,
                'topic'     => $row->slug ?: $row->id,
            ));
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
    public static function getVisitsRecently(
        $days,
        $limit = null,
        $category = null,
        $topic = null,
        $module = null
    ) {
        $module = $module ?: Pi::service('module')->current();
        
        $dateTo   = time();
        $dateFrom = $dateTo - 24 * 3600 * $days;
        
        $where    = array(
            'active'   => 1,
            'status'   => Article::FIELD_STATUS_PUBLISHED,
        );
        
        $modelVisit    = Pi::model('visit', $module);
        $tableVisit    = $modelVisit->getTable();
        $where[$tableVisit . '.time >= ?'] = $dateFrom;
        $where[$tableVisit . '.time < ?']  = $dateTo;
        
        $modelCategory  = Pi::model('category', $module);
        if ($category && $category > 1) {
            $categoryIds = $modelCategory->getDescendantIds($category);

            if ($categoryIds) {
                $where['category'] = $categoryIds;
            }
        }
        
        if ($topic) {
            $where['r.topic'] = $topic;
        }
        
        $modelArticle  = Pi::model('article', $module);
        $modelRelation = Pi::model('article_topic', $module);
        
        $tableArticle  = $modelArticle->getTable();
        $tableRelation = $modelRelation->getTable();
        $select = $modelVisit->select()
            ->columns(array(
                'article',
                'total'      => new Expression('count(*)'),
            ))
            ->join(
                array('a' => $tableArticle),
                sprintf('%s.article = a.id', $tableVisit)
            )
            ->join(
                array('r' => $tableRelation),
                'a.id = r.article'
            )
            ->where($where)
            ->offset(0)
            ->group(array(sprintf('%s.article', $tableVisit)))
            ->limit($limit)
            ->order('total DESC');
        $rowset = $modelVisit->selectWith($select);
        
        $articleIds = array(0);
        foreach ($rowset as $row) {
            $articleIds[] = $row['article'];
        }
        
        $where = array(
            'id' => $articleIds,
        );
        return Entity::getAvailableArticlePage(
            $where,
            1,
            $limit,
            null,
            null,
            $module
        );
    }
}
