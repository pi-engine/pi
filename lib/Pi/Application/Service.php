<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Application;

use Pi;

/**
 * Pi Engine service handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Service
{
    /**
     * Loaded services
     *
     * @var Service\AbstractService[]
     */
    protected static $services = array();

    /**
     * Load a service
     *
     * @param string    $name
     * @param array     $options
     * @return Service\AbstractService
     * @throws \Exception
     */
    public function load($name, $options = array())
    {
        $key = strtolower($name);
        if (!isset(static::$services[$key])) {
            static::$services[$key] = false;
            // Loads service
            $class = sprintf('%s\Service\\%s', __NAMESPACE__, ucfirst($name));
            if (!class_exists($class)) {
                trigger_error(
                    sprintf('Service class "%s" was not loaded.', $class),
                    E_USER_ERROR
                );
                return static::$services[$key];
            }

            static::$services[$key] = new $class($options);
            if (!static::$services[$key] instanceof Service\AbstractService) {
                throw new \Exception(
                    sprintf('Invalid service instantiation "%s"', $name));
            }
            if (method_exists(static::$services[$key], 'shutdown')) {
                Pi::registerShutdown(
                    array(static::$services[$key], 'shutdown')
                );
            }
            if ('log' != $name && $this->hasService('log')) {
                $this->getService('log')->info(
                    sprintf('Service "%s" is loaded', $name)
                );
            }
        }

        return static::$services[$key];
    }

    /**
     * Check if a services is loaded
     *
     * @param string $name
     * @return bool
     */
    public function hasService($name)
    {
        $name = strtolower($name);
        
        return isset(static::$services[$name]) && static::$services[$name];
    }

    /**
     * Get loaded service
     *
     * @param string|null $name
     * @return Service\AbstractService|Service\AbstractService[]
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
