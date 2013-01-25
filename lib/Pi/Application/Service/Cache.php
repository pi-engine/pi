<?php
/**
 * Cache service
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
//use Pi\Cache\Storage\AdapterPluginManager;
use Zend\Cache\StorageFactory;
use Zend\Cache\Storage\Adapter\AbstractAdapter;

class Cache extends AbstractService
{
    protected $fileIdentifier = 'cache';
    protected $nsDelimiter = '_';

    /**
     * Cache storage adapter
     * @var AbstractAdapter
     */
    protected $storage;

    /**
     * Get cache storage, instantiate it if not exist yet
     *
     * @return AbstractAdapter
     */
    public function storage()
    {
        if (!$this->storage) {
            $this->storage = $this->loadStorage($this->options);
        }

        return $this->storage;
    }

    /**
     * Loads cache storage
     *
     * @param array|string $config
     *                  'adapter' - storage adapter; 'plugins'; 'options'
     * @return AbstractAdapter
     */
    public function loadStorage($config = array())
    {
        if (is_string($config)) {
            $configFile = sprintf('cache.%s.php', $config);
            $config = Pi::config()->load($configFile);
        }
        if (!isset($config['adapter']['options']['namespace'])) {
            $config['adapter']['options']['namespace'] = '';
        }
        $config['adapter']['options']['namespace'] = $this->getNamespace($config['adapter']['options']['namespace']);
        //StorageFactory::setAdapterPluginManager(new AdapterPluginManager);
        $storage = StorageFactory::factory($config);

        return $storage;
    }

    /**
     * Set namespace to current cache storage adapter
     *
     * @param string $namespace
     * @params AbstractAdapter $storage
     * @return Cache
     */
    public function setNamespace($namespace = '', $storage = null)
    {
        $namespace = $this->getNamespace($namespace);
        $storage = $storage ?: $this->storage();
        $storage->getOptions()->setNamespace($namespace);
        return $this;
    }

    /**
     * Get canonized namespace by prepending Pi Engine identifier
     *
     * @param string $namespace
     * @return string
     */
    public function getNamespace($namespace = '')
    {
        return $namespace ? Pi::config('identifier') . $this->nsDelimiter . $namespace : Pi::config('identifier');
    }

    /**
     * Clear cache by namespace to current cache storage adapter
     *
     * @param string $namespace
     * @params AbstractAdapter $storage
     * @return Cache
     */
    public function clearByNamespace($namespace = '', $storage = null)
    {
        $namespace = $this->getNamespace($namespace);
        $storage = $storage ?: $this->storage();
        if (method_exists($storage, 'clearByNamespace')) {
            $storage->clearByNamespace($namespace);
        }
        return $this;
    }

    /**
     * Canonize cache key
     *
     * @param string $key Raw key
     * @param string $cacheLevel Cache level
     * @return string
     */
    public function canonizeKey($key, $cacheLevel = '')
    {
        switch ($cacheLevel) {
            case 'user':
                $prefix = Pi::registry('user') ? Pi::registry('user')->id : '';
                break;
            case 'role':
                $prefix = Pi::registry('user') ? Pi::registry('user')->role : '';
                break;
            case 'locale':
                $prefix = Pi::config('locale');
                break;
            default:
                $prefix = '';
                break;
        }
        if ($prefix) {
            $key = $key ? $prefix . $this->nsDelimiter . $key : $prefix;
        }
        $key = str_replace(array(':', '-', '.', '/'), '_', $key);

        return $key;
    }

    /**
     * Set item with namespace
     *
     * @param  string $key
     * @param  mixed  $value
     * @param  string|array $options
     * @params AbstractAdapter $storage
     * @return Cache
     */
    public function setItem($key, $value, $options = array(), $storage = null)
    {
        $storage = $storage ?: $this->storage();
        $storageOptions = $storage->getOptions();

        $namespace      = null;
        $ttl            = null;
        $namespaceOld   = null;
        $ttlOld         = null;
        if (is_string($options)) {
            $namespace = $options;
            $options = array();
        } else {
            if (isset($options['ttl'])) {
                $ttl = $options['ttl'];
            }
            if (isset($options['namespace'])) {
                $namespace = $options['namespace'];
            }
        }
        if (null !== $namespace) {
            $namespaceOld = $storageOptions->namespace;
            $storageOptions->namespace = $this->getNamespace($namespace);
        }
        if (null !== $ttl) {
            $ttlOld = $storageOptions->ttl;
            $storageOptions->ttl = $ttl;
        }

        $storage->setItem($key, $value);
        if (null !== $namespaceOld) {
            $storageOptions->namespace = $namespaceOld;
        }
        if (null !== $ttlOld) {
            $storageOptions->ttl = $ttlOld;
        }

        return $this;
    }

    /**
     * Get item with namespace
     *
     * @param  string $key
     * @param  string|array $options
     * @params AbstractAdapter $storage
     * @return mixed
     */
    public function getItem($key, $options = array(), $storage = null)
    {
        $storage = $storage ?: $this->storage();
        $storageOptions = $storage->getOptions();

        $namespace      = null;
        $ttl            = null;
        $namespaceOld   = null;
        $ttlOld         = null;
        if (is_string($options)) {
            $namespace = $options;
            $options = array();
        } else {
            if (isset($options['ttl'])) {
                $ttl = $options['ttl'];
            }
            if (isset($options['namespace'])) {
                $namespace = $options['namespace'];
            }
        }
        if (null !== $namespace) {
            $namespaceOld = $storageOptions->namespace;
            $storageOptions->namespace = $this->getNamespace($namespace);
        }
        if (null !== $ttl) {
            $ttlOld = $storageOptions->ttl;
            $storageOptions->ttl = $ttl;
        }

        $data = $storage->getItem($key);
        if (null !== $namespaceOld) {
            $storageOptions->namespace = $namespaceOld;
        }
        if (null !== $ttlOld) {
            $storageOptions->ttl = $ttlOld;
        }

        return $data;
    }

    /**
     * Remove item with namespace
     *
     * @param  string $key
     * @param  string|array $options
     * @params AbstractAdapter $storage
     * @return Cache
     */
    public function removeItem($key, $options = array(), $storage = null)
    {
        $storage = $storage ?: $this->storage();
        $storageOptions = $storage->getOptions();

        $namespace      = null;
        $namespaceOld   = null;
        if (is_array($options)) {
            if (isset($options['namespace'])) {
                $namespace = $options['namespace'];
            }
        } else {
            $namespace = $options;
        }
        if ($namespace) {
            $namespaceOld = $storageOptions->namespace;
            $storageOptions->namespace = $this->getNamespace($namespace);
        }
        $data = $storage->removeItem($key);
        if (null !== $namespaceOld) {
            $storageOptions->namespace = $namespaceOld;
        }

        return $data;
    }
}
