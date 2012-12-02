<?php
/**
 * Pi Engine sevice class
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
 * @since           3.0
 * @version         $Id$
 */

namespace Pi\Application;
use Pi;

class Service
{
    protected static $services = array();

    public function __construct()
    {
    }

    public function load($name, $options = array())
    {
        $key = strtolower($name);
        if (!isset(static::$services[$key])) {
            static::$services[$key] = false;
            // Loads service
            $class = sprintf('%s\\Service\\%s', __NAMESPACE__, ucfirst($name));
            if (!class_exists($class)) {
                trigger_error(sprintf('Service class "%s" was not loaded.', $class), E_USER_ERROR);
                return static::$services[$key];
            }

            static::$services[$key] = new $class($options);
            if (!(static::$services[$key] instanceof Service\AbstractService)) {
                throw new \Exception(sprintf('Invalid service instantiation "%s"', $name));
            }
            if (method_exists(static::$services[$key], 'shutdown')) {
                Pi::registerShutdown(array(static::$services[$key], 'shutdown'));
            }
            if ($this->hasService('log')) {
                $this->getService('log')->info(sprintf('Service "%s" is loaded', $name));
            }
        }

        return static::$services[$key];
    }

    /**
     * Check if a services is loaded
     */
    public function hasService($name)
    {
        $name = strtolower($name);
        return isset(static::$services[$name]);
    }

    /**
     * Get loaded service
     */
    public function getService($name = null)
    {
        if (null === $name) {
            return static::$services;
        }
        $name = strtolower($name);
        if (isset(static::$services[$name])) {
            return static::$services[$name];
        }

        return null;
    }
}
