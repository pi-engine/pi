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
     * Get url to a tag
     *
     * @param string $tag
     * @param string $module
     * @param string $type
     *
     * @return string
     */
    public function url($tag, $module = null, $type = '')
    {
        if (null === $module) {
            $module = Pi::service('module')->current();
        }
        $params = array(
            'module'        => $this->module,
            'controller'    => 'index',
            'action'        => 'list',
            'tag'           => $tag
        );
        if ($module) {
            $params['m'] = $module;
            if ($type) {
                $params['type'] = $type;
            }
        }
        $url = Pi::service('url')->assemble('default', $params);

        return $url;
    }

    /**
     * Render a tag
     *
     * @param string $tag
     * @param string $module
     * @param string $type
     *
     * @return string
     */
    public function render($tag, $module = null, $type = '')
    {
        $url    = $this->url($tag, $module, $type);
        $html   = '<a href="' . $url . '" title="' . _escape($tag)
                . '" target="_blank">' . _escape($tag) . '</a>';

        return $html;
    }

    /**
     * Get tags of an item or multi-items
     *
     * @param string     $module Module name
     * @param string|array     $item   Item identifier
     * @param string     $type   Item type, default as ''
     * @param bool
     *
     * @return string[]
     */
    public function get($module, $item, $type = '', $render = false)
    {
        $result = array();

        $items  = (array) $item;
        $rowset = Pi::model('link', $this->module)->select(array(
            'module'    => $module,
            'type'      => $type,
            'item'      => $items,
        ));
        foreach ($rowset as $row) {
            $result[$row['item']][] = $render
                ? $this->render($row['term'], $module, $type)
                : $row['term'];
        }
        if (is_scalar($item)) {
            if (isset($result[$item])) {
                $result = $result[$item];
            } else {
                $result = array();
            }
        }

        return $result;
    }

    /**
     * Add tags of an item
     *
     * @param string       $module Module name
     * @param string       $item   Item identifier
     * @param string       $type   Item type, default as ''
     * @param array|string $tags   Tags to add
     * @param int          $time   Time adding the tags
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
        foreach ($rowset as $row) {
            $tagsExist[$row->term] = $row->toArray();
        }

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
                'term'      => $tag,
                'module'    => $module,
                'type'      => $type,
                'item'      => $item,
                'time'      => $time,
                'order'     => $index
            ));
            $row->save();
        }

        $rowset = $modelStats->select(array(
            'term'      => $tags,
            'module'    => $module,
            'type'      => $type,
        ));
        $statsExist = array();
        foreach ($rowset as $row) {
            $statsExist[$row->term] = $row->toArray();
        }
        foreach ($tags as $tag) {
            if (!isset($statsExist[$tag])) {
                $row = $modelStats->createRow(array(
                    'term'      => $tag,
                    'module'    => $module,
                    'type'      => $type,
                    'count'     => 0,
                ));
                $row->save();
            }
        }

        $modelTag->increment('count', array('term' => $tags));
        $modelStats->increment('count', array('term' => $tags));

        return true;
    }

    /**
     * Update tag list of an item
     *
     * @param string       $module Module name
     * @param string       $item   Item identifier
     * @param string       $type   Item type, default as ''
     * @param array|string $tags   Tags to add
     * @param int          $time   Time adding new tags
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
                'term'      => $tagsDelete,
                'module'    => $module,
                'type'      => $type,
            );
            Pi::model('link', $this->module)->delete($where);
            $where = array(
                'term'      => $tagsDelete,
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
     * @param string $module Module name
     * @param string $item   Item identifier
     * @param string $type   Item type, default as ''
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
     * Get list of items having a tag
     *
     * @param string $tag    Tag
     * @param string $module Module name
     * @param string|null  $type   Item type, null for all types
     * @param int          $limit  Limit
     * @param int          $offset Offset
     *
     * @return array
     */
    public function getList(
        $tag    = '',
        $module = '',
        $type   = '',
        $limit  = 0,
        $offset = 0
    ) {
        $where = array();
        if ($module) {
            $where['module'] = $module;
            if (null !== $type) {
                $where['type'] = $type;
            }
        }
        if ($tag) {
            $where['term'] = $tag;
        }
        $modelLink = Pi::model('link', $this->module);
        $select = $modelLink->select();
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
            $result[] = $row->toArray();
        }

        return $result;
    }

    /**
     * Get count of items of having a tag
     *
     * @param string|array $tag    Tag or conditions
     * @param string  $module Module name
     * @param string $type   Item type
     *
     * @return int
     */
    public function getCount($tag = '', $module = '', $type = '')
    {
         if (is_array($tag)) {
            $where = $tag;
        } elseif (!$module) {
             $where = array();
             if ($tag) {
                 $where['term'] = $tag;
             }
         } else {
            $where = array(
                'module'    => $module,
            );
            if (null !== $type) {
                $where['type'] = $type;
            }
             if ($tag) {
                 $where['term'] = $tag;
             }
        }
        $count = Pi::model('link', $this->module)->count($where);

        return $count;
    }

    /**
     * Get matched host tags for quick match, for typeahead purpose
     *
     * @param string     $term   Term
     * @param int        $limit  Limit
     * @param string     $module Module name
     * @param string     $type   Item type
     * @param string|array $order
     *
     * @return array
     */
    public function match($term, $limit = 5, $module = '', $type = '', $order = '')
    {
        $result = array();

        $columns = array('term', 'count');
        if (!$module) {
            $model = Pi::model('tag', $this->module);
            $where = array();
        } else {
            $model = Pi::model('stats', $this->module);
            $where = array('module' => $module);
            if (null !== $type) {
                $where['type'] = $type;
            } else {
                $columns = array(
                    'term',
                    'count' => new Expression('SUM(count)')
                );
            }
        }

        if (!$order) {
            $order = array('count DESC', 'term ASC');
        }
        $where = Pi::db()->where($where)->like('term', "{$term}%");
        $select = $model->select()
            ->columns($columns)
            ->where($where)
            ->limit($limit)
            ->order($order);
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
     * @param int   $offset
     *
     * @return array
     */
    public function top($limit = 10, $module = '', $type = '', $offset)
    {
        $result = array();
        $where = array();
        $columns = array('term', 'count');
        if (!$module) {
            $model = Pi::model('tag', $this->module);
        } else {
            $model = Pi::model('stats', $this->module);
            $where = array('module' => $module);
            if (null !== $type) {
                $where['type'] = $type;
            } else {
                $columns = array(
                    'term',
                    'count' => new Expression('SUM(count)')
                );
            }
        }
        $select = $model->select()
            ->columns($columns)
            ->where($where)
            ->limit($limit)
            ->order('count DESC');
        if ($module) {
            $select->group('term');
        }
        if ($offset) {
            $select->offset($offset);
        }
        $rowset = $model->selectWith($select);
        foreach ($rowset as $row) {
            $result[] = array(
                'term'  => $row['term'],
                'count' => $row['count'],
            );
        }

        return $result;
    }
}
