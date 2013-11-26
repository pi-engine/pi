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
use Pi\Application\AbstractApi;

/**
 * Module API calls
 *
 * Samples:
 *
 * - Call a module's specified API defined in its API class in
 *  `Module\<ModuleName>\Api\Api`
 *
 * ```
 *  Pi::service('api')->demo('method', $args);
 *  Pi::service('api')->demo->method($args);
 *  Pi::api('demo')->method($args);
 * ```
 *
 * - Call a module's API defined in custom class in
 *  `Module\<ModuleName>\Api\Callback`
 *
 * ```
 *  Pi::service('api')->demo(array('callback', 'method'), $args);
 *  Pi::api('demo', 'callback')->method($args);
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Api extends AbstractService
{
    /**
     * Container for module API handler
     *
     * @var array
     */
    protected $container = array();

    /**
     * Instantiate module API handler
     *
     * @param string $module
     * @param string $name
     * @return AbstractApi|bool
     */
    public function handler($module, $name = 'api')
    {
        $directory = Pi::service('module')->directory($module);
        $class = sprintf(
            'Module\\%s\Api\\%s',
            ucfirst($directory),
            ucfirst($name)
        );
        if (!isset($this->container[$class])) {
            $this->container[$class] = class_exists($class)
                ? new $class($module) : false;
        }

        return $this->container[$class];
    }

    /**
     * Magic method to call a module API via varible
     *
     * Call a module API as
     *
     * ```
     *  Pi::service('api')-><module-name>-><api-method>($args);
     * ```
     *
     * @param string    $moduleName
     * @return AbstractApi|bool
     */
    public function __get($moduleName)
    {
        $handler = $this->handler($moduleName, 'api');

        return $handler;
    }

    /**
     * Magic method to call a module API via function
     *
     * Call a module API as
     *
     * <code>
     *  Pi::service('api')-><module-name>(array(<class>, <method>), <args>);
     * </code>
     *
     * @param string $moduleName
     * @param array  $args
     *
     * @internal param string $class
     * @internal param string $method
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
        if ($handler instanceof AbstractApi
            && is_callable(array($handler, $method))
        ) {
            return call_user_func_array(array($handler, $method), $args);
        }

        return null;
    }
}
