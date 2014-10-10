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
use Module\Article\Stats;
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
     * Get recently visited articles
     * 
     * @param int     $dateFrom
     * @param int     $dateTo
     * @param int     $limit
     * @param int     $category
     * @param string  $module
     * @return array 
     */
    public static function getVisitsInPeriod(
        $dateFrom, 
        $dateTo, 
        $limit = null, 
        $category = null, 
        $module = null
    ) {
        $result = $where = array();
        $module = $module ?: Pi::service('module')->current();

        $modelArticle   = Pi::model('article', $module);
        $modelCategory  = Pi::model('category', $module);
        $modelVisit     = Pi::model('visit', $module);

        if (!empty($dateFrom)) {
            $where['time >= ?'] = $dateFrom;
        }
        if (!empty($dateTo)) {
            $where['time <= ?'] = $dateTo;
        }

        if ($category && $category > 1) {
            $categoryIds = $modelCategory->getDescendantIds($category);

            if ($categoryIds) {
                $where['a.category'] = $categoryIds;
            }
        }

        $where['status'] = Article::FIELD_STATUS_PUBLISHED;
        $where['active'] = 1;

        $select = $modelVisit->select()
            ->columns(
                array(
                    'article',
                    'total' => new Expression('count(article)')
                )
            )
            ->join(
                array('a' => $modelArticle->getTable()),
                sprintf('%s.article = a.id', $modelVisit->getTable()),
                array()
            )
            ->where($where)
            ->group(array(sprintf('%s.article', $modelVisit->getTable())))
            ->order('total DESC');

        if ($limit) {
            $select->limit($limit);
        }

        $resultSetVisit = $modelVisit->selectWith($select)->toArray();
        foreach ($resultSetVisit as $row) {
            $result[$row['article']] = $row;
        }

        $articleIds = array_keys($result);
        if ($articleIds) {
            $resultSetArticle = self::getAvailableArticlePage(
                array('id' => $articleIds), 
                1, 
                $limit, 
                null, 
                null, 
                $module
            );

            foreach ($result as $key => &$row) {
                if (isset($resultSetArticle[$key])) {
                    $row = $row + $resultSetArticle[$key];
                }
            }
        }

        return $result;
    }

    /**
     * Get recently visited articles
     * 
     * @param int     $days
     * @param int     $limit
     * @param int     $category
     * @param string  $module
     * @return array 
     */
    public static function getVisitsRecently(
        $days,
        $limit = null,
        $category = null,
        $module = null
    ) {
        $dateTo   = time();
        $dateFrom = $dateTo - 24 * 3600 * $days;

        return self::getVisitsInPeriod(
            $dateFrom, 
            $dateTo, 
            $limit, 
            $category, 
            $module
        );
    }

    /**
     * Get total visits of articles.
     * 
     * @param int     $limit
     * @param int     $category
     * @param string  $module
     * @return array 
     */
    public static function getTotalVisits(
        $limit = null, 
        $category = null, 
        $module = null
    ) {
        $where = $columns = array();
        $module = $module ?: self::$module;

        $modelCategory  = Pi::model('category', $module);
        if ($category && $category > 1) {
            $categoryIds = $modelCategory->getDescendantIds($category);

            if ($categoryIds) {
                $where['category'] = $categoryIds;
            }
        }
        
        $articles    = Stats::getTopVisits($limit, $module);
        if (!empty($articles)) {
            $where['id'] = array_keys($articles);
        }
        
        $columns = array(
            'id',
            'article' => 'id',
            'subject',
            'source',
            'image',
            'pages',
            'summary',
            'time_publish',
        );

        $result = self::getAvailableArticlePage(
            $where, 
            1, 
            $limit, 
            $columns, 
            null, 
            $module
        );
        
        foreach ($articles as $id => &$article) {
            $article = array_merge($article, $result[$id]);
        }

        return $articles;
    }

    /**
     * Get latest articles
     * 
     * @param int    $limit
     * @param int    $category
     * @param string $module
     * @return array
     */
    public static function getLatest(
        $limit = null, 
        $category = null, 
        $module = null
    ) {
        $where = $columns = array();
        $module = $module ?: self::$module;

        $modelCategory  = Pi::model('category', $module);
        if ($category && $category > 1) {
            $categoryIds = $modelCategory->getDescendantIds($category);

            if ($categoryIds) {
                $where['category'] = $categoryIds;
            }
        }

        $columns = array(
            'id',
            'article' => 'id',
            'total'   => 'visits',
            'subject',
            'source',
            'image',
            'pages',
            'slug',
            'summary',
            'time_publish',
        );

        $result = self::getAvailableArticlePage(
            $where, 
            1, 
            $limit, 
            $columns, 
            null,
            $module
        );

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
        $users  = $articleIds = $userIds = array();

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
        $rowset = $modelArticle->selectWith($select);
        
        /*$resultSet = Pi::model('article', $module)->getSearchRows(
            $where, 
            $limit, 
            $offset, 
            $columns, 
            $order
        );*/

        $resultSet = array();
        if ($rowset->count()) {
            foreach ($rowset as $row) {
                $articleIds[$row->id] = $row->id;

                if (!empty($row->uid)) {
                    $userIds[$row->uid] = $row->uid;
                }
                $resultSet[$row->id] = $row->toArray();
            }
            
            // Get clusters
            $clusters = Pi::api('cluster', $module)->getArticleClusters($articleIds);
            
            // Getting statistics data
            $stats = Pi::model('stats', $module)->getList(array(
                'article' => $articleIds
            ));

            if (!empty($userIds) 
                && (empty($columns) || in_array('uid', $columns))
            ) {
                $users = Pi::user()->get($userIds, array('id', 'name'));
            }

            foreach ($resultSet as &$row) {
                $row = Pi::api('field', $module)->resolver($row);

                if (null === $columns || in_array('uid', $columns)) {
                    if (isset($users[$row['uid']])) {
                        $row['user'] = $users[$row['uid']];
                    }
                }
                if (isset($stats[$row['id']])) {
                    $row['stats'] = $stats[$row['id']];
                }
                if (isset($clusters[$row['id']])) {
                    $row['clusters'] = $clusters[$row['id']];
                }

                $route      = Pi::api('api', $module)->getRouteName();
                $row['url'] = Pi::service('url')->assemble($route, array(
                    'module'    => $module,
                    'time'      => date('Ymd', $row['time_publish']),
                    'id'        => $row['id'],
                ));
            }
        }

        return $resultSet;
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
        
        // Getting stats data
        $modelStatis    = Pi::model('stats', $module);
        $rowStatis      = $modelStatis->find($id, 'article');
        if ($rowStatis) {
            $result['visits'] = $rowStatis->visits;
        }

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
