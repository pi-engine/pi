<?php
/**
 * Registry service
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

class Registry extends AbstractService
{
    // Run-time loaded registries
    //protected $container;
    protected $cache;
    protected $defaultCache;

    public function handler($name, $module = null)
    {
        $key = empty($module) ? $name : $module . '_' . $name;
        /*
        if (isset($this->container[$key])) {
            return $this->container[$key];
        }

        $this->container[$key] = $this->loadHandler($name, $module);
        $this->container[$key]->setCache($this->getCache())->setKey($key);

        return $this->container[$key];
        */
        $handler = $this->loadHandler($name, $module);
        $handler->setCache($this->getCache())->setKey($key);

        return $handler;
    }

    protected function loadHandler($name, $module = null)
    {
        if (empty($module)) {
            $class = sprintf('Pi\\Application\\Registry\\%s', ucfirst($name));
        } else {
            $class = sprintf('Module\\%s\\Registry\\%s', ucfirst($module), ucfirst($name));
        }
        $handler = new $class;
        return $handler;
    }

    /**
     * Remove cache data by namespace
     *
     * @param string     $namespace
     * @return boolean
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
     * Call a registry method as Pi::service('registry')->registryName->registryMethod();
     *
     * @param string    $handlerName
     * @return object
     */
    public function __get($handlerName)
    {
        $handler = $this->handler($handlerName);
        return $handler;
    }

    /**
     * Call a registry method as Pi::service('registry')->registryMethod('registryName', $arg);
     *
     * @param string    $handlerName
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
     * Load cache engine
     */
    protected function defaultCache()
    {
        if (!isset($this->defaultCache)) {
            $this->defaultCache = Pi::service('cache')->storage();
        }

        return $this->defaultCache;
    }

    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    public function getCache()
    {
        if (!isset($this->cache)) {
            $this->cache = $this->defaultCache();
        }
        return $this->cache;
    }

    public function getList()
    {
        $registryList = array();
        $iterator = new \DirectoryIterator(Pi::path('lib/Pi/Application/Registry'));
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isFile() || $fileinfo->isDot()) {
                continue;
            }
            $directory = $fileinfo->getFilename();
            if ('AbstractRegistry.php' == $directory || !preg_match('/^[a-z0-9]+\.php/i', $directory)) {
                continue;
            }
            $registryList[] = strtolower(basename($directory, '.php'));
        }

        return $registryList;
    }
}
