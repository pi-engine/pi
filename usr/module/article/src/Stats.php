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
use Zend\Mvc\MvcEvent;
use Zend\Db\Sql\Expression;
use Module\Article\Model\Article;
use Module\Article\Service;

/**
 * Stats service API
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Stats
{
    protected static $module = 'article';
    
    /**
     * Event listener to run before page cache, so some operation will also work
     * if the page is cached.
     * If this code is added in action, it will be ignored if page is cached.
     * 
     * @param MvcEvent $e 
     */
    public function runBeforePageCache(MvcEvent $e)
    {
        $name = $e->getRouteMatch()->getParam('id');
        if (empty($name)) {
            $name = $e->getRouteMatch()->getParam('slug');
        }
        $module = $e->getRouteMatch()->getParam('module');
        
        self::addVisit($name, $module);
    }

    /**
     * Add visit count and visit record.
     * 
     * @param int|string $name    Article ID or slug
     * @param string     $module  Module name
     */
    public static function addVisit($name, $module = null)
    {
        $module = $module ?: Pi::service('module')->current();
        
        if (!is_numeric($name)) {
            $model = Pi::model('extended', $module);
            $name  = $model->slugToId($name);
        }
        
        Pi::model('stats', $module)->increaseVisits($name);
        Pi::model('visit', $module)->addRow($name);
    }
    
    /**
     * Get articles which are mostly visit.
     * 
     * @param int     $limit   Article limitation
     * @param string  $module
     * @return array 
     */
    public static function getTopVisits($limit, $module = null)
    {
        $module = $module ?: Pi::service('module')->current();
        $model  = Pi::model('stats', $module);
        $select = $model->select()
                        ->limit($limit)
                        ->order('visits DESC');
        $rowset = $model->selectWith($select);
        
        $result = array();
        foreach ($rowset as $row) {
            unset($row->id);
            $result[$row->article] = $row->toArray();
        }
        
        return $result;
    }
    
    /**
     * Get article total count in period.
     * 
     * @param int     $dateFrom  
     * @param int     $dateTo
     * @param string  $module
     * @return int 
     */
    public static function getTotalInPeriod($dateFrom, $dateTo, $module = null)
    {
        $where  = array();
        $module = $module ?: Pi::service('module')->current();

        if (!empty($dateFrom)) {
            $where['time_submit >= ?'] = $dateFrom;
        }
        if (!empty($dateTo)) {
            $where['time_submit <= ?'] = $dateTo;
        }
        $where['status'] = Article::FIELD_STATUS_PUBLISHED;
        $where['active'] = 1;

        $modelArticle   = Pi::model('article', $module);
        $select         = $modelArticle->select()
            ->columns(array('total' => new Expression('count(id)')))
            ->where($where);
        $resultset = $modelArticle->selectWith($select);

        $result = $resultset->current()->total;

        return $result;
    }

    /**
     * Get article total count in period.
     * 
     * @param int     $days
     * @param string  $module
     * @return int 
     */
    public static function getTotalRecently($days = null, $module = null)
    {
        $dateFrom = !is_null($days) ? strtotime(sprintf('-%d day', $days)) : 0;
        $dateTo   = time();

        return self::getTotalInPeriod($dateFrom, $dateTo, $module);
    }

    /**
     * Get total article counts group by category.
     * 
     * @param int     $dateFrom
     * @param int     $dateTo
     * @param string  $module
     * @return int 
     */
    public static function getTotalInPeriodByCategory(
        $dateFrom, 
        $dateTo, 
        $module
    ) {
        $where  = array();
        $module = $module ?: Pi::service('module')->current();

        if (!empty($dateFrom)) {
            $where['time_submit >= ?'] = $dateFrom;
        }
        if (!empty($dateTo)) {
            $where['time_submit <= ?'] = $dateTo;
        }

        $result = Pi::api('api', $module)->getCategoryList();

        foreach ($result as &$val) {
            $val['total'] = 0;
        }

        $modelArticle = Pi::model('article', $module);
        $select = $modelArticle->select()
            ->columns(array(
                'category', 
                'total' => new Expression('count(category)')
            ))
            ->where($where)
            ->group('category');
        $groupResultset = $modelArticle->selectWith($select)->toArray();

        foreach ($groupResultset as $row) {
            $result[$row['category']]['total'] = $row['total'];
        }

        return $result;
    }

    /**
     * Get total article counts group by category
     * 
     * @param int     $days
     * @param string  $module
     * @return int 
     */
    public static function getTotalRecentlyByCategory(
        $days = null, 
        $module = null
    ) {
        $dateFrom = !is_null($days) ? strtotime(sprintf('-%d day', $days)) : 0;
        $dateTo   = time();

        return self::getTotalInPeriodByCategory($dateFrom, $dateTo, $module);
    }

    /**
     * Get submitter count in period.
     * 
     * @param int     $dateFrom
     * @param int     $dateTo
     * @param int     $limit
     * @param string  $module
     * @return int 
     */
    public static function getSubmittersInPeriod(
        $dateFrom, 
        $dateTo, 
        $limit = null, 
        $module = null
    ) {
        $users = $where = array();
        $module = $module ?: Pi::service('module')->current();

        if (!empty($dateFrom)) {
            $where['time_submit >= ?'] = $dateFrom;
        }
        if (!empty($dateTo)) {
            $where['time_submit <= ?'] = $dateTo;
        }
        $where['status'] = Article::FIELD_STATUS_PUBLISHED;
        $where['active'] = 1;

        $modelArticle = Pi::model('article', $module);

        $select = $modelArticle->select()
            ->columns(array('uid', 'total' => new Expression('count(uid)')))
            ->where($where)
            ->group('uid')
            ->order('total DESC');

        if ($limit) {
            $select->limit($limit);
        }

        $result = $modelArticle->selectWith($select)->toArray();

        $userIds = array();
        foreach ($result as $row) {
            if (!empty($row['uid'])) {
                $userIds[] = $row['uid'];
            }
        }
        $userIds = array_unique($userIds);

        if (!empty($userIds)) {
            $resultsetUser = Pi::user()->get($userIds, array('id', 'name'));
            foreach ($resultsetUser as $row) {
                $users[$row['id']] = array(
                    'name' => $row['name'],
                );
            }
            unset($resultsetUser);
        }

        foreach ($result as &$row) {
            if (!empty($users[$row['uid']])) {
                $row['name'] = $users[$row['uid']]['name'];
            }
        }

        return $result;
    }

    /**
     * Get submitter count in period
     * 
     * @param int     $days
     * @param int     $limit
     * @param string  $module
     * @return int 
     */
    public static function getSubmittersRecently(
        $days = null, 
        $limit = null, 
        $module = null
    ) {
        $dateFrom = !is_null($days) ? strtotime(sprintf('-%d day', $days)) : 0;
        $dateTo   = time();

        return self::getSubmittersInPeriod($dateFrom, $dateTo, $limit, $module);
    }
}
