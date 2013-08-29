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
use Pi\Application\Registry\AbstractRegistry;
use Zend\Cache\Storage\Adapter\AbstractAdapter as CacheStorage;

/**
 * Registry service
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Registry extends AbstractService
{
    /**
     * Cache storage
     * @var CacheStorage
     */
    protected $cache;

    /**
     * Default cache storage
     * @var CacheStorage
     */
    protected $defaultCache;

    /**
     * Handler container
     * @var AbstractRegistry[]
     */
    protected $handler = array();

    /**
     * Get registry handler
     *
     * @param string        $name
     * @param sting|null    $module
     * @return AbstractRegistry
     */
    public function handler($name, $module = null)
    {
        $key = empty($module) ? $name : $module . '_' . $name;
        if (!isset($this->handler[$key])) {
            $handler = $this->loadHandler($name, $module);
            $handler->setCache($this->getCache())->setKey($key);
            $this->handler[$key] = $handler;
        }

        return $this->handler[$key];
    }

    /**
     * Load registry handler
     *
     * @param string        $name
     * @param string|null   $module
     * @return AbstractRegistry
     */
    protected function loadHandler($name, $module = null)
    {
        if (empty($module)) {
            $class = sprintf('Pi\Application\Registry\\%s', ucfirst($name));
        } else {
            $class = sprintf(
                'Module\\%s\Registry\\%s',
                ucfirst($module),
                ucfirst($name)
            );
        }
        $handler = new $class;

        return $handler;
    }

    /**
     * Remove cache data by namespace
     *
     * @param string $namespace
     * @return bool
     */
    public function flush($namespace = '')
    {
        $list = $this->getList();
        foreach ($list as $key) {
            $handler = $this->handler($key);
            if ($namespace) {
                $handler->clear($namespace);
            } else {
                $handler->flush();
            }
        }

        return $this;
    }

    /**
     * Magic method to get registry handler
     *
     * Call a registry method as
     *  `Pi::service('registry')-><registry-name>-><registry-method>();`
     *
     * @param string $handlerName
     * @return AbstractRegistry
     */
    public function __get($handlerName)
    {
        $handler = $this->handler($handlerName);

        return $handler;
    }

    /**
     * Magic method to call a registry handler's method
     *
     * Call a registry method as
     * `Pi::service('registry')-><registry-method>(<registry-name>, $args);`
     *
     * @param string $handlerName
     * @param array  $args
     *
     * @return mixed
     */
    public function __call($handlerName, $args)
    {
        $method = array_shift($args);
        $handler = $this->handler($handlerName);
        if (is_callable(array($handler, $method))) {
            return call_user_func_array(array($handler, $method), $args);
        }
    }

    /**
     * Load default cache storage
     *
     * @return CacheStorage
     */
    protected function defaultCache()
    {
        if (!isset($this->defaultCache)) {
            $this->defaultCache = Pi::service('cache')->storage();
        }

        return $this->defaultCache;
    }

    /**
     * Set cache storage
     *
     * @param CacheStorage $cache
     * @return void
     */
    public function setCache(CacheStorage $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Get cache storage
     *
     * @return CacheStorage
     */
    public function getCache()
    {
        if (!isset($this->cache)) {
            $this->cache = $this->defaultCache();
        }

        return $this->cache;
    }

    /**
     * Get available registry list
     *
     * @return string[]
     */
    public function getList()
    {
        $registryList = array();
        $iterator = new \DirectoryIterator(
            Pi::path('lib/Pi/Application/Registry')
        );
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isFile() || $fileinfo->isDot()) {
                continue;
            }
            $directory = $fileinfo->getFilename();
            if ('AbstractRegistry.php' == $directory
                || !preg_match('/^[a-z0-9]+\.php/i', $directory)
            ) {
                continue;
            }
            $registryList[] = strtolower(basename($directory, '.php'));
        }

        return $registryList;
    }
}
