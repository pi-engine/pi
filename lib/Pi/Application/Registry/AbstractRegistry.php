<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Registry
 */

namespace Pi\Application\Registry;

use Pi;
use Zend\Cache\Storage\Adapter\AbstractAdapter as CacheAdapter;

/**
 * Cache registry abstract class
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractRegistry
{
    /**
     * Tag for generating identifier
     *
     * @var string
     */
    const TAG = 'registry';

    /**
     * Identifier
     *
     * @var string
     */
    protected $registryKey;

    /**
     * Raw data generator
     *
     * @var Callback|null
     */
    protected $generator;

    /**
     * Cache storage
     *
     * @var CacheAdapter
     */
    protected $cache;

   /**
     * Namespace of current registry
    *
     * @var string
     */
    protected $namespace;

    /**
     * The meta tags used for namespace,
     * thus will be skipped in key generator
     * since the tags will be prefixed via namespace
     *
     * @var string[]
     */
    protected $namespaceMeta = array();

    /**
     * Data generator
     *
     * @param Callback $generator
     * @return $this
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
     * @return mixed|bool
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
        return sprintf('%s_%s_%s', static::TAG, $this->registryKey, $name);
    }

    /**
     * Set namespace of current meta
     *
     * @param string|array $meta
     * @return $this
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
                throw new \Exception(
                    'Custom namespace is required for registry '
                    . get_class($this)
                );
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
                        $meta[$var] = Pi::service('user')->getUser()->role();
                        break;
                    case 'locale':
                        $meta[$var] = Pi::service('i18n')->locale;
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
     * @return $this
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
     * @return array|bool
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

        return $data;
    }

    /**
     * Load cache data matching the meta
     *
     * @param array $meta
     * @return array|bool
     */
    protected function loadCacheData($meta = array())
    {
        $data = null;
        if ($this->cache()) {
            $cacheKey = $this->createKey($meta);
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
     * @return bool
     */
    protected function saveCache($data, $meta = array())
    {
        if ($data === false) {
            return false;
        }
        $status = false;
        if ($this->cache()) {
            $cacheKey = $this->createKey($meta);
            $data = Pi::service('cache')->setItem(
                $cacheKey,
                json_encode($data),
                $this->namespace
            );
        }

        return $status;
    }

    /**
     * Set registry key
     *
     * @param string $key
     * @return $this
     */
    public function setKey($key)
    {
        $this->registryKey = $key;

        return $this;
    }

    /**
     * Clear cached content
     *
     * @param string $namespace
     * @return $this
     */
    public function clear($namespace = '')
    {
        Pi::service('cache')->clearByNamespace(
            $this->getNamespace($namespace)
        );

        return $this;
    }

    /**
     * Flush all cached contents
     *
     * @return $this
     */
    public function flush()
    {
        $this->flushByModules();

        return $this;
    }

    /**
     * Flush cached contents by modules
     *
     * @return $this
     */
    public function flushByModules()
    {
        $modules = Pi::service('module')->meta();
        foreach (array_keys($modules) as $module) {
            $this->clear($module);
        }

        return $this;
    }

    /**
     * Flush cached contents by sections
     *
     * @return $this
     */
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

    /**
     * Read data from cache storage
     *
     * In case data are not available in cache storage,
     * they will be fetched and stored into cache storage
     *
     * @return array
     */
    abstract public function read();

    /**
     * Create data in cache storage
     *
     * @return bool
     */
    abstract public function create();
}
