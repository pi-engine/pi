<?php
/**
 * Pi registry abstraction
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
 * @subpackage      Registry
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Application\Registry;
use Pi;
use Zend\Cache\Storage\Adapter\AbstractAdapter as CacheAdapter;

abstract class AbstractRegistry
{
    const TAG = 'registry';

    protected $registryKey;

    protected $generator;

    /**
     * Cache storage
     * @var CacheAdapter
     */
    protected $cache;

   /**
     * Namespace of current registry
     * @var string
     */
    protected $namespace;

    /**
     * The meta tags used for namespace, thus will be skipped in key generator since the tags will be prefixed via namespace
     * @var array
     */
    protected $namespaceMeta = array();

    /**
     * Data generator
     *
     * @param type $generator
     * @return AbstractRegistry
     */
    public function setGenerator($generator)
    {
        $this->generator = $generator;
        return $this;
    }

    /**
     * Load dynamic data from database
     *
     * @param array $options
     * @return mixed
     * @throws \Exception
     */
    protected function loadDynamic($options)
    {
        if ($this->generator) {
            return call_user_func($this->generator, $options);
        } else {
            throw new \Exception('Abstract method should be instantiated.');
        }
    }

    /**
     * Get namespace, i.e. namespace prefix of the registry
     *
     * @param string $name
     * @return string
     */
    protected function getNamespace($name)
    {
        //return Pi::service('cache')->getNamespace(sprintf('%s_%s_%s', static::TAG, $this->registryKey, $name));
        return sprintf('%s_%s_%s', static::TAG, $this->registryKey, $name);
    }

    /**
     * Set namespace of current meta
     *
     * @param string|array $meta
     * @return AbstractRegistry
     * @throws \Exception
     */
    public function setNamespace($meta)
    {
        $cache = $this->cache();
        if ($cache) {
            if (is_string($meta)) {
                $namespace = $meta;
            } elseif (isset($meta['module'])) {
                $namespace = $meta['module'];
                $this->namespaceMeta = array('module');
            } else {
                throw new \Exception('Custom namespace is required for registry ' . get_class($this));
            }
            /*
            $namespace = $this->getNamespace($namespace);
            $cache->getOptions()->setNamespace($namespace);
            */
            $this->namespace = $this->getNamespace($namespace);
        }

        return $this;
    }

    /**
     * Normalize value
     *
     * @param string $val
     * @return string
     */
    protected function normalizeValue($val)
    {
        return str_replace(array(':', '-', '.', '/'), '_', strval($val));
    }

    /**
     * Create key according to meta
     *
     * @param array $meta
     * @return string
     */
    protected function createKey($meta = array())
    {
        $key = '';
        foreach (array_keys($meta) as $var) {
            if ($this->namespaceMeta) {
                if (in_array($var, $this->namespaceMeta)) {
                    continue;
                }
            }
            if (null === $meta[$var]) {
                switch ($var) {
                    case 'role':
                        $meta[$var] = Pi::registry('user')->role;
                        break;
                    case 'locale':
                        $meta[$var] = Pi::config('locale');
                        break;
                    default:
                        break;
                }
            }
            if (null !== $meta[$var]) {
                $key .= '_' . $this->normalizeValue($meta[$var]);
            }
        }
        $key = $key ?: static::TAG;
        return $key;
    }

    /**
     * Set cache storage
     *
     * @param CacheAdapter $cache
     * @return AbstractRegistry
     */
    public function setCache(CacheAdapter $cache)
    {
        $this->cache = clone $cache;
        return $this;
    }

    /**
     * Get cache storage
     *
     * @return CacheAdapter
     */
    public function cache()
    {
        if (null === $this->cache) {
            $this->cache = Pi::service('registry')->getCache();
        }

        return $this->cache;
    }

    /**
     * Load data matching the meta
     *
     * @param array $meta
     * @return array
     */
    protected function loadData($meta = array())
    {
        //$isCached = true;
        $this->setNamespace($meta);
        if (null === ($data = $this->loadCacheData($meta))) {
            $data = $this->loadDynamic($meta);
            $this->saveCache($data, $meta);
            //$isCached = false;
        }

        /*
        if (Pi::service()->hasService('log')) {
            Pi::service('log')->info(sprintf('Registry %s is %s.', get_class($this), $isCached ? 'cached' : 'generated'));
        }
        */
        return $data;
    }

    /**
     * Load cache data matching the meta
     *
     * @param array $meta
     * @return array
     */
    protected function loadCacheData($meta = array())
    {
        $data = null;
        if ($this->cache()) {
            $cacheKey = $this->createKey($meta);

            /*
            $namespace = $this->cache->getOptions()->getNamespace();
            $this->cache->getOptions()->setNamespace($this->namespace);
            $data = $this->cache->getItem($cacheKey);
            $this->cache->getOptions()->setNamespace($namespace);
            */

            $data = Pi::service('cache')->getItem($cacheKey, $this->namespace);
            if (null !== $data) {
                $data = json_decode($data, true);
            }
        }
        return $data;
    }

    /**
     * Save data into cache storage
     *
     * @param mixed $data
     * @param array $meta
     * @return boolean
     */
    protected function saveCache($data, $meta = array())
    {
        if ($data === false) {
            return false;
        }
        //return $this->cache() ? $this->cache->setItem($this->createKey($meta), json_encode($data)) : false;
        $status = false;
        if ($this->cache()) {
            /*
            $namespace = $this->cache->getOptions()->getNamespace();
            $this->cache->getOptions()->setNamespace($this->namespace);
            $status = $this->cache->setItem($this->createKey($meta), json_encode($data));
            $this->cache->getOptions()->setNamespace($namespace);
            */
            $cacheKey = $this->createKey($meta);
            $data = Pi::service('cache')->setItem($cacheKey, json_encode($data), $this->namespace);
        }

        return $status;
    }

    public function setKey($key)
    {
        $this->registryKey = $key;
        return $this;
    }

    public function clear($namespace = '')
    {
        /*
        if ($this->cache() && method_exists($this->cache(), 'clearByNamespace')) {
            $this->cache()->clearByNamespace($this->getNamespace($namespace));
        }
        */
        Pi::service('cache')->clearByNamespace($this->getNamespace($namespace));

        return $this;
    }

    public function flush()
    {
        $this->flushByModules();

        return $this;
    }

    public function flushByModules()
    {
        $modules = Pi::service('module')->meta();
        foreach (array_keys($modules) as $module) {
            $this->clear($module);
        }

        return $this;
    }

    public function flushBySections()
    {
        $sections = array(
            'front',
            'admin',
            'feed',
        );
        foreach ($sections as $section) {
            $this->clear($section);
        }

        return $this;
    }
    /*
    public function read() {}
    public function create() {}
    public function delete() {}
    public function flush() {}
    */
}
