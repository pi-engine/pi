<?php
/**
 * API call service
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
use Pi\Application\AbstractApi;

/**
 * Module API calls
 *
 * <code>
 *  // Call a module's specified API defined in its Api class in Module\ModuleName\Api\Api
 *  Pi::service('api')->demo('method', $args);
 *  Pi::service('api')->demo->method($args);
 *
 *  // Call a module's API defined in custom class in Module\ModuleName\Api\Callback
 *  Pi::service('api')->demo(array('callback', 'method'), $args);
 * </code>
 */
class Api extends AbstractService
{
    protected $container = array();

    /**
     * Instantiate module API handler
     *
     * @param string $module
     * @param string $name
     * @return AbstractApi|false
     */
    public function handler($module, $name = 'api')
    {
        $directory = Pi::service('module')->directory($module);
        $class = sprintf('Module\\%s\\Api\\%s', ucfirst($directory), ucfirst($name));
        if (!isset($this->container[$class])) {
            $this->container[$class] = class_exists($class) ? new $class($module) : false;
        }
        return $this->container[$class];
    }

    /**
     * Call a module API as Pi::service('api')->moduleName->apiMethod($args);
     *
     * @param string    $moduleName
     * @return AbstractApi|false
     */
    public function __get($moduleName)
    {
        $handler = $this->handler($moduleName, 'api');
        return $handler;
    }

    /**
     * Call a module API as Pi::service('api')->moduleName(array('class', 'method'), $args);
     *
     * @param string    $moduleName
     * @param string    $class
     * @param string    $method
     * @param array     $args
     * @return mixed
     */
    public function __call($moduleName, $args)
    {
        $callback = array_shift($args);
        if (is_string($callback)) {
            list($class, $method) = array('api', $callback);
        } else {
            list($class, $method) = $callback;
        }
        $handler = $this->handler($moduleName, $class);
        if ($handler instanceof AbstractApi && is_callable(array($handler, $method))) {
            return call_user_func_array(array($handler, $method), $args);
        }
        return null;
    }
}
