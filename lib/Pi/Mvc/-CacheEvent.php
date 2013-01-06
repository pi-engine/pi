<?php
/**
 * Cache Event class
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
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @since           3.0
 * @package         Pi\Mvc
 * @version         $Id$
 */

namespace Pi\Mvc;

use Pi;
use Zend\Cache\Storage\Adapter\AbstractAdapter;
//use Zend\EventManager\Event;

/**
 * Cache handler for view rendering
 *
 * @see Pi\Application\Resource\Cache
 */
class CacheEvent //extends Event
{
    /**
     * Cache Storage
     *
     * @var AbstractAdapter
     */
    protected $storage;

    /**
     * Content is allowed to cache
     *
     * @var boolean
     */
    protected $cachable = true;

    /**
     * Caching is opened
     *
     * @var boolean
     */
    protected $opened = false;

    /**
     * Cache meta
     * @var array
     */
    protected $meta = array(
        'key'       => '',
        'ttl'       => 0,
        'namespace' => '',
        'level'     => ''
    );

    /**
     * Cached content
     * @var string
     */
    protected $cachedContent = null;

    /**
     * Generated content
     * @var string
     */
    protected $content;

    /**
     * Canonize cache adapter namespace by prepending 'render_'
     *
     * @param string $namespace
     * @return string
     */
    protected function canonizeNamespace($namespace)
    {
        return $namespace;
    }

    /**
     * Canonize cache key by prepending theme name
     *
     * @param string $key
     * @return string
     */
    protected function canonizeKey($key)
    {
        return Pi::service('theme')->current() . '_' . $key;
    }

    /**
     * Get/set cache storage
     *
     * @param AbstractAdapter|null
     * @return AbstractAdapter|CacheEvent
     */
    public function storage($storage = null)
    {
        if ($storage instanceof AbstractAdapter) {
            $this->storage = $storage;
            return $this;
        }
        return $this->storage;
    }

    /**
     * Check if content is allowed to cache in a specific context
     *
     * @param boolean $flag
     * @return boolean
     */
    public function isCachable($flag = null)
    {
        if (null !== $flag) {
            $this->cachable = (bool) $flag;
        }
        return $this->cachable;
    }

    /**
     * Check if cache is opened in a specific context
     *
     * @param boolean $flag
     * @return boolean
     */
    public function isOpened($flag = null)
    {
        if (null !== $flag) {
            $this->opened = (bool) $flag;
        }
        return $this->opened;
    }

    /**
     * Check if cache content is available
     *
     * @return bool
     */
    public function isCached()
    {
        $isCached = true;
        if (!$this->isCachable() || null === $this->cachedContent()) {
            $isCached = false;
        }
        // TODO
        return $isCached;
    }

    /**
     * Get/set meta
     *
     * @param string        $meta
     * @param mixed|null    $value
     * @return Cache|mixed
     */
    public function meta($meta, $value = null)
    {
        if (null === $value) {
            return isset($this->meta[$meta]) ? $this->meta[$meta] : null;
        }
        if ('namespace' == $meta) {
            $value = $this->canonizeNamespace($value);
        }
        if ('key' == $meta) {
            $value = $this->canonizeKey($value);
        }
        $this->meta[$meta] = $value;
        return $this;
    }

    /**
     * set generated content
     *
     * @param string $content
     * @return CacheEvent
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Get generated content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Load content from cache storage
     *
     * @return string|false
     */
    public function cachedContent()
    {
        if (null === $this->cachedContent) {
            //$this->cachedContent = $this->storage()->getItem($this->meta['key']);
            $this->cachedContent = Pi::service('cache')->getItem($this->meta['key'], $this->meta['namespace'], $this->storage());
        }
        return $this->cachedContent;
    }

    /**
     * Save content to cache storage
     *
     * @param string $content
     * @return CacheEvent
     */
    public function saveCache($content = null)
    {
        $content = (null !== $content) ? $content : $this->content;
        if (null !== $content) {
            Pi::service('cache')->setItem($this->meta['key'], $content, $this->meta, $this->storage());
        }
        $this->opened = false;
        return $this;
    }
}
