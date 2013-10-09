<?php
/**
 * Tag module default API class
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Chuang Liu <liuchuang@eefocus.com>
 * @since           3.0
 * @package         Module\Tag
 * @version         $Id$
 */

namespace Module\Tag\Api;

use Pi\Application\AbstractApi;
use Zend\Db\Sql\Expression;
use Pi;

class Api extends AbstractApi
{
    protected static $moduleName = 'tag';

    /**
     * Search relate article according to tag array.
     *
     * @param  array  $tags   Tag array
     * @param  string $module Module name
     * @param  string $type   Item type
     * @param  int    $limit  Return item id counts
     * @return array  $result     Item id array
     */
    public function relate($tags, $module, $type, $limit = null, $item = null)
    {
        $offset = 0;
        $tags = is_scalar($tags) ? array($tags) : $tags;
        $tags = array_unique($tags);

        $modelTag = Pi::model('tag', static::$moduleName);
        $modelLink = Pi::model('link', static::$moduleName);

        // Switch tagName to tagId
        $select = $modelTag->select()->where(array('term' => $tags));
        $rowset = $modelTag->selectWith($select)->toArray();
        foreach ($rowset as $row) {
            $tagIds[] = $row['id'];
        }
        if (null !== $item) {
            $item = (int) $item;
            $where = array('tag' => $tagIds, 'item != ?' => $item);
        } else {
            $where = array('tag' => $tagIds);
        }
        $select = $modelLink->select()->where($where)
                                      ->columns(array('item' => new Expression('distinct item')))
                                      ->order('time DESC');
        if (null !== $limit) {
            $limit = intval($limit);
            $select->offset($offset)->limit($limit);
        }
        $rowset = $modelLink->selectWith($select)->toArray();

        // Change $rowset to Digital index array
        $result = array_map(function($val) {
            return isset($val['item']) ? $val['item'] : null;
        }, $rowset);

        return $result;
    }

    /**
     * Fetch top tag and coount
     *
     * @param string $module Moudle name
     * @param type   $type   Item type
     * @param type   $limit  Return tag count
     */
    public static function top($module, $type, $limit = null)
    {
        $offset = 0;
        $modelTag = Pi::model('tag', static::$moduleName);
        $modelStats = Pi::model('stats', static::$moduleName);
        $where = array('module' => $module);
        if (!empty($type)) {
            $where['type'] = $type;
        }
        $select = $modelStats->select()->where($where)->order('count DESC');
        if (null !== $limit) {
            $limit = intval($limit);
            $select->offset($offset)->limit($limit);
        }
        $rowset = $modelStats->selectWith($select)->toArray();
        foreach ($rowset as $row) {
            $tagIds[] = $row['tag'];
        }
        $select = $modelTag->select()->where(array('id' => $tagIds))->order('count DESC');
        $result = $modelTag->selectWith($select)->toArray();
        return $result;
    }

    /**
     * Freth some item releate tag
     *
     * @param  string $module  module name not null
     * @param  array  $items   items array
     * @param  string $type    items type  default null
     * @return array  result   items relate tags
     */
    public static function multiple($module, $items, $type = null)
    {
        $items      = is_scalar($items) ? (array) $items : $items;
        $result     = array();

        $modeTag    = Pi::model('tag', static::$moduleName);
        $modeLink   = Pi::model('link', static::$moduleName);

        $where      = array('item' => $items, 'module' => $module);
        if ($type) {
            $where['type'] = $type;
        }

        // Get item releate tag ids
        $select = $modeLink->select()->where($where)->order('order ASC')->columns(array('tag', 'item'));
        $rows = $modeLink->selectWith($select)->toArray();
        $tagIds = array();
        foreach ($rows as $row) {
            $result[$row['item']][$row['tag']] = '';
            $tagIds[] = $row['tag'];
        }
        if (empty($tagIds)) {
            return array();
        }
        $tagIds = array_unique($tagIds);
        $select = $modeTag->select()->where(array('id' => $tagIds))->columns(array('id', 'term'));
        $rowset = $modeTag->selectWith($select)->toArray();
        foreach ($rowset as $row) {
            $terms[$row['id']] = $row['term'];
        }

        foreach($result as $index => $row) {
            foreach ($row as $key => $value) {
                $result[$index][$key] = $terms[$key];
            }
        }
        return $result;
    }
}
