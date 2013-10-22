<?php
/**
 * Tag module service class
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

namespace Module\Tag;

use Zend\Db\Sql\Expression;
use Pi;

class Service
{
    protected static $moduleName = 'tag';

    /**
     * Add tags of an item
     *
     * @param  string       $module Module name
     * @param  string       $item   Item identifier
     * @param  string       $type   Item type, default as ''
     * @param  array|string $tags   Tags to add
     * @param  int|null     $time   Time adding the tags
     * @return bool
     */
    public static function add($module, $item, $type, $tags, $time = null)
    {
        // Change params type
        $module = strval($module);
        $item = intval($item);
        $time = $time ? intval($time) : time();
        $tags = array_unique($tags);
        foreach ($tags as $index => $tag) {
            // Filter space
            $tag = trim($tag);
            if ('' === $tag) {
                continue;
            }
            // new tag in table or not
            $tagModel = Pi::model('tag', static::$moduleName);
            $select = $tagModel->select()->where(array('term' => $tag));
            $result = $tagModel->selectWith($select)->current();
            // tag not in the tag table, insert, or update.
            if (false === $result) {
                $data = array('term' => $tag, 'count' => 1);
                $row = $tagModel->createRow($data);
                $row->save();
                $tagId = intval($row->id);
            } else {
                $tagId = $result->id;
                $tagModel->update(array('count' => new Expression('count + 1')), array('id' => $tagId));
            }

            // Insert data to link table.
            $data = array('tag' => $tagId, 'module' => $module, 'item' => $item, 'time' => $time, 'order' => $index);
            if ($type != null) {
                $data['type'] = $type;
            }
            $row = Pi::model('link', static::$moduleName)->createRow($data);
            $row->save();

            // Search tag information in stats table.
            $statsModel = Pi::model('stats', static::$moduleName);
            $where = array('tag' => $tagId, 'module' => $module);
            if ($type != null) {
                $where['type'] = $type;
            }
            $select = $statsModel->select()->where($where);
            $statsResult = $statsModel->selectWith($select)->current();

            if (false === $statsResult) {
                $data = array('tag' => $tagId, 'module' => $module, 'count' => 1);
                if ($type != null) {
                    $where['type'] = $type;
                }
                $row = Pi::model('stats', static::$moduleName)->createRow($data);
                $row->save();
            } else {
                $statsId = $statsResult->id;
                $statsModel->update(array('count' => new Expression('count + 1')), array('id' => intval($statsId)));
            }
        }
    }

    /**
     * Update tag list of an item
     *
     * @param  string       $module Module name
     * @param  string       $item   Item identifier
     * @param  string       $type   Item type
     * @param  array|string $tags   Tags to add
     * @param  int|null     $time   Time adding new tags
     * @return bool
     */
    public static function update($module, $item, $type, $tags, $time)
    {
        // Change params type
        $module = strval($module);
        $item   = intval($item);
        $time   = intval($time);
        $tags   = array_unique($tags);
        $update = 0;

        // database model
        $tagModel   = Pi::model('tag', static::$moduleName);
        $linkModel  = Pi::model('link', static::$moduleName);
        $statsModel = Pi::model('stats', static::$moduleName);

        // Get item relaeated tag and order
        $oldTags = static::get($module, $item, $type);
        if (count($tags) == count($oldTags)) {
            foreach ($tags as $key => $value) {
                if ($oldTags[$key] != $value) {
                    $update = 1;
                }
            }
        } else {
            $update = 1;
        }

        if ($update == 0) {
            return ;
        }

        foreach ($tags as $key => $tag) {
            // tag is exist,order not modify ,ignore
            $tag = trim($tag);
            if ('' === $tag) {
                continue;
            }
            // tag is exist, order diff
            $tagId = static::in_tag($tag);

            if ( ! empty($tagId) && in_array($tag, $oldTags)) {
                // Update link table
                $linkModel->update(array('order' => $key), array('tag' => $tagId));
                continue;
            }


            // update tag table
            if (!empty($tagId)) {
                $tagModel->update(array('count' => new Expression('count + 1')), array('id' => $tagId));
            } else {
                $data = array('term' => $tag, 'count' => 1);
                $row = $tagModel->createRow($data);
                $row->save();
                $tagId = $row->id;
            }

            // update link table
            $data = array('tag' => $tagId, 'item' => $item, 'order' => $key, 'module' => $module, 'time' => $time);
            if ($type != null) {
                $data['type'] = $type;
            }
            $row = $linkModel->createRow($data);
            $row->save();

            // update stats table
            $statsId = static::in_stats($tagId);
            if (!empty($statsId)) {
                $statsModel->update(array('count' => new Expression('count + 1')), array('id' => intval($statsId)));
            } else {
                $data = array('tag' => $tagId, 'module' => $module, 'count' => 1);
                if ($type != null) {
                    $where['type'] = $type;
                }
                $row = $statsModel->createRow($data);
                $row->save();
            }
        }

        // Delete old tag
        $deleteTag = array_diff($oldTags, $tags);
        if (empty($deleteTag)) {
            return ;
        }
        $select = $tagModel->select()->where(array('term' => $deleteTag));
        $rowset = $tagModel->selectWith($select);
        $deleteTagId = array();
        foreach ($rowset as $row) {
            $deleteTagId[] = $row->id;
        }
        foreach ($deleteTagId as $tag) {

            $where = array('item' => $item, 'tag' => $tag, 'module' => $module,);
            if (!empty($type)) {
                $where['type'] = $type;
            }
            $select = $linkModel->select()->where($where);
            $result = $linkModel->selectWith($select)->toArray();

            // Delete data from link table.
            $linkModel->delete($where);

            // Delete data from stats table.
            foreach ($result as $row) {
                $where = array('tag' => $row['tag'], 'module' => $module);
                if (!empty($type)) {
                    $where['type'] = $type;
                }
                $select = $statsModel->select()->where($where);
                $statsCount = $statsModel->selectWith($select)->current()->count;

                if (1 == $statsCount) {
                    $statsModel->delete($where);
                } elseif (1 < $statsCount) {
                    $statsModel->update(array('count' => new Expression('count - 1')), $where);
                }
            }

            // Delete data from tag table.
            foreach ($result as $row) {
                $tagModel->update(array('count' =>  new Expression('count - 1')), array('id' => $row['tag']));
            }
        }
    }

    /**
     * Delete tags of an item
     *
     * @param  string $module Module name
     * @param  string $item   Item identifier
     * @param  string $type   Item type, default as ''
     * @return bool
     */
    public static function delete($module, $item, $type = '')
    {
        // Change params type
        $module = strval($module);
        $items    = is_scalar($item) ? array($item) : $item;
        $type = strval($type);

        foreach($items as $item) {
            // Search relevent obj's tag.
            $linkModel = Pi::model('link', static::$moduleName);
            $where = array('item' => $item, 'module' => $module);
            if (!empty($type)) {
                $where['type'] = $type;
            }
            $select = $linkModel->select()->where($where);
            $result = $linkModel->selectWith($select)->toArray();

            // Delete data from link table.
            $linkModel->delete($where);

            // Delete data from stats table.
            foreach ($result as $row) {
                $statsModle = Pi::model('stats', static::$moduleName);
                $where = array('tag' => $row['tag'], 'module' => $module);
                if (!empty($type)) {
                    $where['type'] = $type;
                }
                $select = $statsModle->select()->where($where);
                $statsCount = $statsModle->selectWith($select)->current()->count;

                if (1 == $statsCount) {
                    $statsModle->delete($where);
                } elseif (1 < $statsCount) {
                    $statsModle->update(array('count' => new Expression('count - 1')), $where);
                }
            }

            // Delete data from tag table.
            foreach ($result as $row) {
                $tagModel = Pi::model('tag', static::$moduleName);
                $tagModel->update(array('count' =>  new Expression('count - 1')), array('id' => $row['tag']));
            }
        }
    }

    /**
     * Get tags of an item
     *
     * @param  string     $module Module name
     * @param  string     $item   Item identifier
     * @param  string     $type   Item type
     * @return array|bool
     */
    public static function get($module, $item, $type = null)
    {
        $tagArray = array();
        $tagid = array();
        $linkModel = Pi::model('link', static::$moduleName);
        if (null == $type) {
            $where = array('item' => $item, 'module' => $module);
        } else {
            $where = array('item' => $item, 'module' => $module, 'type' => $type);
        }
        $select = $linkModel->select()->where($where)->order('order ASC');

        $result = $linkModel->selectWith($select)->toArray();
        foreach ($result as $row) {
            $tagid[] = $row['tag'];
            $tagName[$row['tag']] = '';
        }
        if (empty($tagid)) {
            return array();
        }
        $tagModel = Pi::model('tag', static::$moduleName);
        if(empty($tagid)) {
            return array();
        }
        $select = $tagModel->select()->where(array('id' => $tagid));
        $rowset = $tagModel->selectWith($select);

        foreach ($rowset as $row) {
            $tagName[$row->id] = $row->term;
        }
        foreach ($tagName as $name) {
            $tagArray[] = $name;
        }

        return $tagArray;
    }

    /**
     * Get item list of a tag
     *
     * @param  string       $module Module name
     * @param  string|array $tag    Tag
     * @param  string|null  $type   Item type, null for all types
     * @param  int          $limit  Limit
     * @param  int          $offset Offset
     * @return array|bool
     */
    public static function getList($module, $tag, $type = null, $limit = null, $offset = 0)
    {
        // Change data type
        $offset = intval($offset);
        $tag = array_unique($tag);
        $tagIds = array();

        // Get tag array id.
        $modelTag = Pi::model('tag', static::$moduleName);
        $select = $modelTag->select()->where(array('term' => $tag))->columns(array('id'));
        $data = $modelTag->selectWith($select)->toArray();

        foreach ($data as $row) {
            $tagIds[] = $row['id'];
        }
        $where = array();
        if (!empty($tagIds)) {
        $where = array('tag' => $tagIds, 'module' => $module);
        } else {
            return array();
        }
        if (null !== $type) {
            $where['type'] = $type;
        }
        $modelLink = Pi::model('link', static::$moduleName);
        $select = $modelLink->select()->where($where)->order('time DESC')->columns(array('item' => new Expression('distinct item')));
        if (null !== $limit) {
            $limit = intval($limit);
            $select->offset($offset)->limit($limit);
        }
        $re = $modelLink->selectWith($select)->toArray();
        $result = array();
        foreach($re as $id) {
            $result[] = $id['item'];
        }

        return $result;
    }

    /**
     * Get count items of tags
     *
     * @param  string       $module Module name
     * @param  string|array $tag    Tag
     * @param  string|null  $type   Item type, null for all types
     * @return int|bool
     */
    public static function getCount($module, $tag, $type = null)
    {
        $tagIds = array();
        $modelTag = Pi::model('tag', static::$moduleName);
        $tag = array_unique($tag);
        $select = $modelTag->select()->where(array('term' => $tag))->columns(array('id'));
        $rowset = $modelTag->selectWith($select);

        if (0 === $rowset->count()) {
            return 0;
        }

        foreach ($rowset as $row) {
            $tagIds[] = $row->id;
        }

        if (empty($tagIds)) {
            return 0;
        }
        $modelLink = Pi::model('link', static::$moduleName);
        $where = array('tag' => $tagIds, 'module' => $module);
        if (null !== $type) {
            $where['type'] = $type;
        }
        $select = $modelLink->select()->where($where)->columns(array('items' => new Expression('distinct item')));
        $count = $modelLink->selectWith($select)->count();

        return $count;
    }

    /**
     * Get matched tags for quick match
     *
     * @param  string     $term   Term
     * @param  int        $limit  Limit
     * @param  string     $module Module name, null for all modules
     * @param  string     $type   Item type, null for all types
     * @return array|bool
     */
    public static function match($term, $limit, $module = null, $type = null)
    {
        $limit = intval($limit);
        $offset = 0;
        $result = array();
        $tagIds = array();

        if ((null === $module) || (null === $type)) {
            // Get website tag
            $tagModel = Pi::model('tag', static::$moduleName);
            $select = $tagModel->select();
            $select->where->like('term', "{$term}%");
            $select->order('term ASC')->offset($offset)->limit($limit);
            $resultset = $tagModel->selectWith($select)->toArray();
            foreach ($resultset as $row) {
                $result[] = $row['term'];
            }

        } elseif ((null !== $module) && (null !== $type)) {
            $modelStats = Pi::model('stats', static::$moduleName);
            $modelTag = Pi::model('tag', static::$moduleName);

            // Search tag table
            $select = $modelTag->select();
            $select->where->like('term', "{$term}%");
            $rowset = $modelTag->selectWith($select)->toArray();
            foreach ($rowset as $row) {
                $tagIds[] = $row['id'];
            }

            if (empty($tagIds)) {
                return array();
            }
            // Search stats table
            $select = $modelStats->select()->where(array('tag' => $tagIds, 'module' => $module, 'type' => $type))->offset($offset)->limit($limit);
            $rowset = $modelStats->selectWith($select)->toArray();

            $tagIds = array();
            foreach ($rowset as $row) {
                $tagIds[] = $row['tag'];
            }

            // Search tag table for tagName
            $select = $modelTag->select()->where(array('id' => $tagIds))->columns(array('term'));
            $rowset = $modelTag->selectWith($select)->toArray();
            foreach ($rowset as $row) {
                $result[] = $row['term'];
            }
        }

        return $result;
    }

    /**
     * tag is ni tag database
     *
     * @param string $tag
     */
    public static function in_tag($tag)
    {
        $tagModel = Pi::model('tag', static::$moduleName);
        $select = $tagModel->select()->where(array('term' => $tag));
        $rowset = $tagModel->selectWith($select)->current();
        if($rowset) {
            return $rowset->id;
        }
    }

    public static function in_stats($tagId)
    {
        $statsModel = Pi::model('stats', static::$moduleName);
        $select = $statsModel->select()->where(array('tag' => $tagId));
        $rowset = $statsModel->selectWith($select)->current();
        if ($rowset) {
            return $rowset->id;
        }
    }
}