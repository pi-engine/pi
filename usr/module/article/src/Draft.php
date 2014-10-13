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
use Module\Article\Model\Article;
use Module\Article\Model\Draft as DraftModel;

/**
 * Common draft API
 * 
 * @author Zongshu Lin <lin40553024@163.com> 
 */
class Draft
{
    protected static $module = 'article';
    
    /**
     * Get draft page
     * 
     * @param array  $where
     * @param int    $page
     * @param int    $limit
     * @param array  $columns
     * @param string $order
     * @param string $module
     * @return array
     */
    public static function getDraftPage(
        $where, 
        $page, 
        $limit, 
        $columns = null, 
        $order = null, 
        $module = null
    ) {
        $offset  = ($limit && $page) ? $limit * ($page - 1) : null;

        $module  = $module ?: Pi::service('module')->current();
        $users   = $userIds = array();

        $resultSet = (array) Pi::model('draft', $module)->getSearchRows(
            $where,
            $limit,
            $offset,
            $columns,
            $order
        );

        foreach ($resultSet as $row) {
            if (!empty($row['uid'])) {
                $userIds[$row['uid']] = $row['uid'];
            }
        }

        if (!empty($userIds)) {
            $users = Pi::user()->get($userIds, array('id', 'name'));
        }

        foreach ($resultSet as &$row) {
            $row = Pi::api('field', $module)->resolver($row);
            if (empty($columns) || isset($columns['uid'])) {
                if (!empty($users[$row['uid']])) {
                    $row['user'] = $users[$row['uid']];
                }
            }
        }

        return $resultSet;
    }

    /**
     * Break article content by delimiter
     * 
     * @param string  $content
     * @return array
     */
    public static function breakPage($content)
    {
        $result = $matches = $row = array();
        $page   = 0;

        $matches = preg_split(
            Article::PAGE_BREAK_PATTERN, 
            $content, 
            null, 
            PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
        );
        foreach ($matches as $text) {
            if (preg_match(Article::PAGE_BREAK_PATTERN, $text)) {
                if (isset($row['title']) || isset($row['content'])) {
                    $row['page'] = ++$page;
                    $result[] = $row;
                    $row = array();
                }

                $text = strip_tags($text);
                if (preg_replace('/&nbsp;/', '', trim($text)) !== '') {
                    $row['title'] = trim($text);
                } else {
                    $row['title'] = '';
                }
            } else {
                $row['content'] = trim($text);
            }
        }

        if (!empty($row)) {
            $row['page'] = ++$page;
            $result[]    = $row;
        }

        return $result;
    }

    /**
     * Generate article summary
     * 
     * @param string  $content
     * @param int     $length
     * @return string
     */
    public static function generateArticleSummary($content, $length)
    {
        // Remove title
        $result = preg_replace(
            array(Article::PAGE_BREAK_PATTERN, '/&nbsp;/'), 
            '', 
            $content
        );
        // Strip tags
        $result = preg_replace('/<[^>]*>/', '', $result);
        // Trim blanks
        $result = trim($result);
        // Limit length
        $result = mb_substr($result, 0, $length, 'utf-8');

        return $result;
    }
    
    /**
     * Change status number to slug string
     * 
     * @param int  $status
     * @return string 
     */
    public static function getStatusSlug($status)
    {
        $slug = '';
        switch ($status) {
            case DraftModel::FIELD_STATUS_DRAFT:
                $slug = 'draft';
                break;
            case DraftModel::FIELD_STATUS_PENDING:
                $slug = 'pending';
                break;
            case DraftModel::FIELD_STATUS_REJECTED:
                $slug = 'approve';
                break;
            case Article::FIELD_STATUS_PUBLISHED:
                $slug = 'publish';
                break;
            default:
                $slug = 'draft';
                break;
        }
        
        return $slug;
    }
}
