<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Tag\Api;

use Zend\Db\Sql\Expression;
use Pi\Application\Api\AbstractApi;
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
     * @param  int     $time   Time adding the tags
     *
     * @return bool
     */
    public function add($module, $item, $type, $tags, $time = 0)
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
     * @param  int     $time   Time adding new tags
     *
     * @return bool
     */
    public function update($module, $item, $type, $tags, $time = 0)
    {
        return Pi::api('api', 'tag')->update($module, $item, $type, $tags, $time);
    }

    /**
     * Delete tags of an item
     *
     * @param  string $module Module name
     * @param  string $item   Item identifier
     * @param  string $type   Item type
     *
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
     * @param  string|array     $item   Item identifier
     * @param  string     $type   Item type
     *
     * @return array
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
}
