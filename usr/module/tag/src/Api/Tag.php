<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Tag\Api;

use Zend\Db\Sql\Expression;
use Pi\Application\AbstractApi;
use Pi;

/**
 * Tag APIs
 *
 * @author Liu Chuang <liuchuangww@gmail.com>
 */
class Tag extends AbstractApi
{
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
    public function add($module, $item, $type, $tags, $time = null)
    {
        return Pi::api('api', 'tag')->add($module, $item, $type, $tags, $time);
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
    public function update($module, $item, $type, $tags, $time)
    {
        return Pi::api('api', 'tag')->update($module, $item, $type, $tags, $time);
    }

    /**
     * Delete tags of an item
     *
     * @param  string $module Module name
     * @param  string $item   Item identifier
     * @param  string $type   Item type, default as ''
     * @return bool
     */
    public function delete($module, $item, $type = '')
    {
        return Pi::api('api', 'tag')->delete($module, $item, $type);
    }

    /**
     * Get tags of an item
     *
     * @param  string     $module Module name
     * @param  string     $item   Item identifier
     * @param  string     $type   Item type
     * @return array|bool
     */
    public function get($module, $item, $type = null)
    {
        return Pi::api('api', 'tag')->get($module, $item, $type);
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
    public function getList($module, $tag, $type = null, $limit = null, $offset = 0)
    {
        return Pi::api('api', 'tag')->getList($module, $tag, $type, $limit, $offset);
    }

    /**
     * Get count items of tags
     *
     * @param  string       $module Module name
     * @param  string|array $tag    Tag
     * @param  string|null  $type   Item type, null for all types
     * @return int|bool
     */
    public function getCount($module, $tag, $type = null)
    {
        return Pi::api('api', 'tag')->getCount($module, $tag, $type);
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
    public function match($term, $limit, $module = null, $type = null)
    {
        return Pi::api('api', 'tag')->match($term, $limit, $module, $type);
    }

    /**
     * Search relate article according to tag array.
     *
     * @param  array  $tags   Tag array
     * @param  string $module Module name
     * @param  string $type   Item type
     * @param  int    $limit  Return item id counts
     * @param  null|string    $item
     *
     * @return array  $result     Item id array
     */
    public function relate($tags, $module, $type, $limit = null, $item = null)
    {
        return Pi::api('api', 'tag')->relate($tags, $module, $type, $limit, $item);
    }

    /**
     * Fetch top tag and count
     *
     * @param string $module Module name
     * @param string   $type   Item type
     * @param int   $limit  Return tag count
     *
     * @return array
     */
    public function top($module, $type, $limit = null)
    {
        return Pi::api('api', 'tag')->top($module, $type, $limit);
    }

    /**
     * Fetch multiple item related tags
     *
     * @param  string $module  module name not null
     * @param  array  $items   items array
     * @param  string $type    items type  default null
     *
     * @return array  result   items relate tags
     */
    public function multiple($module, $items, $type = null)
    {
        return Pi::api('api', 'tag')->multiple($module, $items, $type);
    }
}
