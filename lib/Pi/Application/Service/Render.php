<?php
/**
 * Render cache service
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
 * @package         Pi\Application
 * @subpackage      Service
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Application\Service;

use Pi;
use Zend\Cache\Storage\Adapter\AbstractAdapter;

/**
 * Cache handler for view rendering
 *
 * @see Pi\Application\Resource\Cache
 */
class Render extends AbstractService
{
    /**
     * Cache Storage
     *
     * @var AbstractAdapter|string
     */
    protected $storage = 'filesystem';

    /**
     * Rendering type, potential values: page, action, block
     *
     * @var string
     */
    protected $type = 'page';

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
     * Canonize cache key by prepending theme name and rendering type
     *
     * @param string $key
     * @return string
     */
    protected function canonizeKey($key)
    {
        return Pi::service('theme')->current() . '_' . $this->getType() . '_' . $key;
    }

    /**
     * Set cache storage
     *
     * @param AbstractAdapter
     * @return Render
     */
    public function setStorage($storage)
    {
        if ($storage instanceof AbstractAdapter) {
            $this->storage = $storage;
        }
        return $this;
    }

    /**
     * Get cache storage
     *
     * @return AbstractAdapter
     */
    public function getStorage()
    {
        if (!$this->storage instanceof AbstractAdapter) {
            $storage = !empty($this->options['storage']) ? $this->options['storage'] : ($this->storage ?: '');
            if ($storage) {
                $storage = Pi::service('cache')->loadStorage($storage);
            } else {
                $storage = Pi::service('cache')->storage();
            }
            $this->storage = $storage;
        }

        return $this->storage;
    }

    /**
     * Set rendering type
     *
     * @param string $type
     * @return Render
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get rendering type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
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
     * @return Render|mixed
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
     * @return Render
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
        $key = $this->meta['key'];
        if (!isset($this->cachedContent[$key])) {
            $this->cachedContent[$key] = Pi::service('cache')->getItem($this->meta['key'], $this->meta, $this->getStorage());
        }
        return $this->cachedContent[$key];
    }

    /**
     * Save content to cache storage
     *
     * @param string $content
     * @return Render
     */
    public function saveCache($content = null)
    {
        $content = (null !== $content) ? $content : $this->content;
        if (null !== $content) {
            Pi::service('cache')->setItem($this->meta['key'], $content, $this->meta, $this->getStorage());
        }
        $this->opened = false;
        return $this;
    }

    /**
     * Flush render cache: a specific item, specified namespace or all
     *
     * @param string|null $namespace    Namespace for cache storage, usually module name
     * @param string|null $key
     * @return Render
     */
    public function flushCache($namespace = null, $key = null)
    {
        // Remove an item
        if (null !== $key) {
            Pi::service('cache')->removeItem($namespace, $key, $this->getStorage());
            return $this;
        }

        // Flush by namespace
        if (null !== $namespace) {
            Pi::service('cache')->clearByNamespace($namespace, $this->getStorage());
            return $this;
        }

        // Flush all by modules
        $modules = Pi::service('module')->meta();
        foreach (array_keys($modules) as $module) {
            Pi::service('cache')->clearByNamespace($module, $this->getStorage());
        }
    }
}