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

use Pi;
use Pi\Application\AbstractApi;
use Zend\Db\Sql\Expression;

/**
 * Tag API
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @author Liu Chuang <liuchuangww@gmail.com>
 */
class Api extends AbstractApi
{
    /**
     * @{inheritDoc}
     */
    protected $module = 'tag';

    /**
     * Fetch tags from text
     *
     * @param string|string[] $tags
     *
     * @return string[]
     */
    public function canonize($tags)
    {
        if (is_string($tags)) {
            $tags = preg_split('#[\|\s\,]+#', $tags, 0, PREG_SPLIT_NO_EMPTY);
        }
        $tags = array_unique(array_filter(array_map('trim', $tags)));

        return $tags;
    }

    /**
     * Add tags of an item
     *
     * @param  string       $module Module name
     * @param  string       $item   Item identifier
     * @param  string       $type   Item type, default as ''
     * @param  array|string $tags   Tags to add
     * @param  int          $time   Time adding the tags
     *
     * @return bool
     */
    public function add($module, $item, $type, $tags, $time = 0)
    {
        $time = $time ?: time();
        $tags = $this->canonize($tags);

        if (!$tags) {
            return true;
        }

        $modelTag   = Pi::model('tag', $this->module);
        $modelLink  = Pi::model('link', $this->module);
        $modelStats = Pi::model('stats', $this->module);

        $rowset = $modelTag->select(array('term' => $tags));
        $tagsExist = array();
        array_walk($rowset, function ($row) use (&$tagsExist) {
            $tagsExist[$row->term] = $row->toArray();
        });

        foreach ($tags as $index => $tag) {
            if (!isset($tagsExist[$tag])) {
                $row = $modelTag->createRow(array(
                    'term'  => $tag,
                    'count' => 0,
                ));
                $row->save();
            }

            // Insert data to link table
            $row = $modelLink->createRow(array(
                'tag'       => $tag,
                'module'    => $module,
                'type'      => $type,
                'item'      => $item,
                'time'      => $time,
                'order'     => $index
            ));
            $row->save();
        }

        $rowset = $modelStats->select(array(
            'tag'       => $tags,
            'module'    => $module,
            'type'      => $type,
        ));
        $statsExist = array();
        array_walk($rowset, function ($row) use (&$statsExist) {
            $statsExist[$row->tag] = $row->toArray();
        });
        foreach ($tags as $tag) {
            if (!isset($statsExist[$tag])) {
                $row = $modelStats->createRow(array(
                    'tag'       => $tags,
                    'module'    => $module,
                    'type'      => $type,
                    'count'     => 0,
                ));
                $row->save();
            }
        }

        $modelTag->increment('count', array('term' => $tags));
        $modelStats->increment('count', array('tag' => $tags));

        return true;
    }

    /**
     * Update tag list of an item
     *
     * @param  string       $module Module name
     * @param  string       $item   Item identifier
     * @param  string       $type   Item type
     * @param  array|string $tags   Tags to add
     * @param  int          $time   Time adding new tags
     *
     * @return bool
     */
    public function update($module, $item, $type, $tags, $time = 0)
    {
        $tags       = $this->canonize($tags);
        $tagsExist  = $this->get($module, $item, $type);
        $tagsNew    = array_diff($tags, $tagsExist);
        if ($tagsNew) {
            $this->add($module, $item, $type, $tagsNew, $time);
        }
        $tagsDelete = array_diff($tagsExist, $tags);
        if ($tagsDelete) {
            $where = array(
                'item'      => $item,
                'tag'       => $tagsDelete,
                'module'    => $module,
                'type'      => $type,
            );
            Pi::model('link', $this->module)->delete($where);
            $where = array(
                'tag'       => $tagsDelete,
                'module'    => $module,
                'type'      => $type,
            );
            Pi::model('stats', $this->module)->increment('count', $where, -1);
        }

        return true;
    }

    /**
     * Delete tags of an item
     *
     * @param  string $module Module name
     * @param  string $item   Item identifier
     * @param  string $type   Item type, default as ''
     *
     * @return bool
     */
    public function delete($module, $item, $type = '')
    {
        $tags = $this->get($module, $item, $type);
        if (!$tags) {
            return true;
        }

        Pi::model('tag', $this->module)->increment('count', array(
            'term'  => $tags
        ), -1);
        Pi::model('stats', $this->module)->increment('count', array(
            'module'    => $module,
            'type'      => $type,
            'term'      => $tags
        ), -1);
        Pi::model('link', $this->module)->delete(array(
            'module'    => $module,
            'type'      => $type,
            'item'      => $item,
        ));

        return true;
    }

    /**
     * Get tags of an item
     *
     * @param  string     $module Module name
     * @param  string     $item   Item identifier
     * @param  string     $type   Item type
     * @return string[]
     */
    public function get($module, $item, $type = '')
    {
        $tags = array();
        $model = Pi::model('link', $this->module);
        $where = array('item' => $item, 'module' => $module, 'type' => $type);
        $select = $model->select()->where($where)->order('order ASC');
        $rowset = $model->selectWith($select);
        foreach ($rowset as $row) {
            $tags[] = $row['tag'];
        }

        return $tags;
    }

    /**
     * Get list of items having a tag
     *
     * @param  string       $module Module name
     * @param  string       $tag    Tag
     * @param  string|null  $type   Item type, null for all types
     * @param  int          $limit  Limit
     * @param  int          $offset Offset
     *
     * @return int[]
     */
    public function getList($module, $tag, $type = null, $limit = 0, $offset = 0)
    {
        $where = array('module' => $module, 'tag' => $tag);
        if (null !== $type) {
            $where['type'] = $type;
        }
        $modelLink = Pi::model('link', $this->module);
        $select = $modelLink->select();
        $select->columns(array('item' => new Expression('distinct item')));
        $select->where($where)->order('time DESC');
        if ($limit) {
            $select->limit($limit);
        }
        if ($offset) {
            $select->offset($offset);
        }
        $rowset = $modelLink->selectWith($select);
        $result = array();
        foreach($rowset as $row) {
            $result[] = $row['item'];
        }

        return $result;
    }

    /**
     * Get count of items of having a tag
     *
     * @param  string       $module Module name
     * @param  string       $tag    Tag
     * @param  string|null  $type   Item type, null for all types
     *
     * @return int
     */
    public function getCount($module, $tag, $type = null)
    {
        $where = array('module' => $module, 'tag' => $tag);
        if (null !== $type) {
            $where['type'] = $type;
        }
        $count = Pi::model('link', $this->module)->count($where);

        return $count;
    }

    /**
     * Get matched tags for quick match
     *
     * @param  string     $term   Term
     * @param  int        $limit  Limit
     * @param  string     $module Module name, null for all modules
     * @param  string     $type   Item type, null for all types
     *
     * @return array
     */
    public function match($term, $limit, $module = null, $type = null)
    {
        $result = array();
        $where = array();
        if ($module) {
            $where['module'] = $module;
            if (null !== $type) {
                $where['type'] = $type;
            }
        }
        $where = Pi::db()->where($where);
        $where->like('term', "{$term}%");
        $model = Pi::model('tag', $this->module);
        $select = $model->select()
            ->where($where)
            ->limit($limit)
            ->order('term ASC');
        $rowset = $model->selectWith($select);
        foreach ($rowset as $row) {
            $result[] = $row['term'];
        }

        return $result;
    }

    /**
     * Fetch top tags and item count
     *
     * @param string $module Module name
     * @param string|null   $type   Item type
     * @param int   $limit  Return tag count
     *
     * @return array
     */
    public function top($module = null, $type = null, $limit = 0)
    {
        $result = array();
        if (!$module) {
            $modelTag = Pi::model('tag', $this->module);
            $select = $modelTag->select()->order('count DESC');
            if ($limit) {
                $select->limit($limit);
            }
            $rowset = $modelTag->selectWith($select);
        } else {
            $modelLink = Pi::model('link', $this->module);
            $select = $modelLink->select();
            $select->columns(array(
                'tag',
                'count' => new Expression('count(*)')
            ));
            $select->order('count DESC');
            if ($limit) {
                $select->limit($limit);
            }
            $where = array('module' => $module);
            if (null !== $type) {
                $where['type'] = $type;
            }
            $select->where($where);
            $rowset = $modelLink->selectWith($select);

        }
        foreach ($rowset as $row) {
            $result[] = array(
                'tag'   => $row['tag'],
                'count' => $row['count'],
            );
        }

        return $result;
    }

    /**
     * Fetch tags shared by multiple items
     *
     * @param  string $module  module name not null
     * @param  array  $items   items array
     * @param  string $type    items type
     *
     * @return array  result   items relate tags
     */
    public function multiple($module, $items, $type = '')
    {
        $result = array();
        $rowset = Pi::model('link', $this->module)->select(array(
            'module'    => $module,
            'type'      => $type,
            'item'      => $items,
        ));
        foreach ($rowset as $row) {
            $result[$row['item']][] = $row['tag'];
        }

        return $result;
    }
}
