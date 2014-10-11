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

/**
 * Stats service API
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Stats
{
    protected static $module = 'article';
    
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

        $result = Pi::api('category', $module)->getList();

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
    
    /**
     * Get article stats data
     * 
     * @param int[]       $ids
     * @param string|null $range  Values: 'D', 'W', 'M', 'A', all data available as default
     * @return int|array
     */
    public static function getTotalVisit($ids, $range = null)
    {
        $result = array();
        
        if (empty($ids)) {
            return $result;
        }
        
        $module = Pi::service('module')->current();
        
        $where  = array('article' => $ids);
        if (null !== $range) {
            $where['date'] = $range;
        }
        $rowset = Pi::model('stats', $module)->select($where);
        foreach ($rowset as $row) {
            if (null !== $range && is_scalar($range)) {
                $result[$row->article] = $row->visits;
            } else {
                $result[$row->article][$row->date] = $row->visits;
            }
        }
        
        if (is_scalar($ids)) {
            $result = $result[$ids];
        }
        
        return $result;
    }
}
