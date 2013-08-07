<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;
use Module\Tag\Service as TagService;

/**
 * Tag service
 *
 * <code>
 *  Pi::service('tag')->add('article', 5, '', array('news', 'tech'));
 *  Pi::service('tag')->add('article', 5, '', 'news');
 *
 *  Pi::service('tag')->update('article', 5, '', array('news', 'tech'));
 *  Pi::service('tag')->update('article', 5, '', 'news');
 *  Pi::service('tag')->update('article', 5, '', array());
 *
 *  Pi::service('tag')->delete('article', 5, '');
 *  Pi::service('tag')->delete('article', 5);
 *
 *  Pi::service('tag')->get('article', 5, '');
 *
 *  Pi::service('tag')->getList('article', array('news', 'tech'), '', 100, 90);
 *  Pi::service('tag')->getList('article', 'news', null, 100);
 *
 *  Pi::service('tag')->getCount('article', 'news', '');
 *  Pi::service('tag')->getCount('article', array('news', 'tech'), null);
 *
 *  Pi::service('tag')->match('n', 5, 'article');
 *  Pi::service('tag')->match('new', 5);
 * </code>
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Tag extends AbstractService
{
    /**
     * Whether or not the service is active, or tag module is activated
     *
     * @var bool
     */
    protected $active = null;

    /**
     * Is tag service available
     *
     * @return bool
     */
    public function active()
    {
        if (null === $this->active) {
            $this->active = Pi::service('module')->isActive('tag');
        }

        return $this->active;
    }

    /**
     * Add tags of an item
     *
     * @param string $module        Module name
     * @param string $item          Item identifier
     * @param string $type          Item type, default as ''
     * @param array|string  $tags   Tags to add
     * @param int|null $time        Time adding the tags
     * @return bool
     */
    public function add($module, $item, $type, $tags, $time = null)
    {
        if (!$this->active()) {
            return false;
        }
        $time = $time ?: time();
        $tags = (array) $tags;

        return TagService::add($module, $item, $type, $tags, $time);
    }

    /**
     * Update tag list of an item
     *
     * @param string $module        Module name
     * @param string $item          Item identifier
     * @param string $type          Item type, default as ''
     * @param array|string  $tags   Tags to add
     * @param int|null $time        Time adding new tags
     * @return bool
     */
    public function update($module, $item, $type, $tags, $time = null)
    {
        if (!$this->active()) {
            return false;
        }
        $time = $time ?: time();
        $tags = (array) $tags;

        return TagService::update($module, $item, $type, $tags, $time);
    }

    /**
     * Delete tags of an item
     *
     * @param string $module        Module name
     * @param string $item          Item identifier
     * @param string $type          Item type, default as ''
     * @return bool
     */
    public function delete($module, $item, $type = '')
    {
        if (!$this->active()) {
            return false;
        }

        return TagService::delete($module, $item, $type);
    }

    /**
     * Get tags of an item
     *
     * @param string $module        Module name
     * @param string $item          Item identifier
     * @param string $type          Item type
     * @return array|bool
     */
    public function get($module, $item, $type = '')
    {
        if (!$this->active()) {
            return false;
        }

        return TagService::get($module, $item, $type);
    }

    /**
     * Get item list of a tag
     *
     * @param string $module        Module name
     * @param string|array $tag     Tag
     * @param string|null $type     Item type, null for all types
     * @param int    $limit         Limit
     * @param int    $offset        Offset
     * @return array|bool
     */
    public function getList(
        $module,
        $tag,
        $type = null,
        $limit = null,
        $offset = 0
    ) {
        if (!$this->active()) {
            return false;
        }

        return TagService::getList(
            $module,
            (array) $tag,
            $type,
            $limit,
            $offset
        );
    }

    /**
     * Get count items of tags
     *
     * @param string $module        Module name
     * @param string|array $tag     Tag
     * @param string|null $type     Item type, null for all types
     * @return int|bool
     */
    public function getCount($module, $tag, $type = null)
    {
        if (!$this->active()) {
            return false;
        }

        return TagService::getCount($module, (array) $tag, $type);
    }

    /**
     * Get matched tags for quick match
     *
     * @param string $term          Term
     * @param int    $limit         Limit
     * @param string $module        Module name, null for all modules
     * @param string $type          Item type, null for all types
     * @return array|bool
     */
    public function match($term, $limit, $module = null, $type = null)
    {
        if (!$this->active()) {
            return false;
        }

        return TagService::match($term, $limit, $module, $type);
    }

    /**
     * Undefined method handler allows a shortcut
     *
     * @param  string  $method  priority name
     * @param  array  $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        if (!$this->active()) {
            return false;
        }
        if (method_exists('TagService', $method)) {
            return call_user_func_array(array('TagService', $method), $args);
        }
        
        return null;
    }
}
