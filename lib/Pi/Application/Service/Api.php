<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;
use Pi\Application\Api\AbstractApi;

/**
 * API calls
 *
 * Samples:
 *
 * - Call a module's specified API defined in its API classes in
 *  `Module\<ModuleName>\Api\<ApiName>`
 *
 * ```
 *  $handler = Pi::service('api')->apiName(<module>, $args);
 *  $handler = Pi::service('api')->handler(<api>, <module>, $args);
 *  $handler->method($args);
 *
 *  Pi::api(<api_name>, <module>)->method($args);
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
     * Instantiate API handler
     *
     * @param string $api
     * @param string|array $module
     * @param array $options
     *
     * @return AbstractApi|bool
     */
    public function handler($api, $module = '', array $options = array())
    {
        if (is_array($module)) {
            $options= $module;
            $module = '';
        }
        // Load system service
        if (!$module) {
            $handler = Pi::service($api, $options);

            return $handler;
        }
        // Load module API
        $directory = Pi::service('module')->directory($module);
        $class = sprintf(
            'Module\\%s\Api\\%s',
            ucfirst($directory),
            ucfirst($api)
        );
        if (!isset($this->container[$class])) {
            $this->container[$class] = class_exists($class)
                ? new $class($module) : false;
        }

        return $this->container[$class];
    }

    /**
     * Magic method to call a system service API via variable
     *
     * ```
     *  Pi::service('api')-><service-name>-><api-method>($args);
     * ```
     *
     * @param string    $name
     * @return AbstractApi|bool
     */
    public function __get($name)
    {
        $handler = $this->handler($name);

        return $handler;
    }

    /**
     * Magic method to call a module API via function
     *
     * Call a module API as
     *
     * <code>
     *  Pi::service('api')-><api-name>(<module>, <options>);
     * </code>
     *
     * @param string $api
     * @param array  $args
     *
     * @return AbstractApi|bool
     */
    public function __call($api, array $args = array())
    {
        $module = '';
        if ($args && is_string($args[0])) {
            $module = array_shift($args);
        }
        $handler = $this->handler($api, $module, $args);

        return $handler;
    }
}
