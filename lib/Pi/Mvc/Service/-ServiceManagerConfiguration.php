<?php
/**
 * MVC service configuration
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
 * @since           3.0
 * @package         Pi\Mvc
 * @version         $Id$
 */

namespace Pi\Mvc\Service;

use Zend\Mvc\Service\ServiceManagerConfiguration as ZendServiceManagerConfiguration;

class ServiceManagerConfiguration extends ZendServiceManagerConfiguration
{
    /**
     * Services that can be instantiated without factories
     *
     * @var array
     */
    protected $invokables = array(
        'SharedEventManager'        => 'Zend\EventManager\SharedEventManager',

        /**#@+
         * From ModuleManagerFactory
         */
        'ViewManager'               => 'Pi\Mvc\View\ViewManager',
        'ViewHelperManager'         => 'Pi\Mvc\Service\ViewHelperManager',
        'Configuration'             => 'Pi\Mvc\Service\Configuration',
        'ControllerLoader'          => 'Pi\Mvc\Service\ControllerLoader',
        'ControllerPluginManager'   => 'Pi\Mvc\Controller\PluginManager',

        'DispatchListener'          => 'Zend\Mvc\DispatchListener',
        'Request'                   => 'Zend\Http\PhpEnvironment\Request',
        'Response'                  => 'Zend\Http\PhpEnvironment\Response',
        'RouteListener'             => 'Zend\Mvc\RouteListener',
        /**#@-*/
    );

    /**
     * Service factories
     *
     * @var array
     */
    protected $factories = array(
        'EventManager'              => 'Zend\Mvc\Service\EventManagerFactory',
        'ModuleManager'             => 'Pi\Mvc\Service\ModuleManagerFactory',

        /**#@+
         * From ModuleManagerFactory
         */
        'Application'               => 'Pi\Mvc\Service\ApplicationFactory',

        'DependencyInjector'        => 'Zend\Mvc\Service\DiFactory',
        'ViewFeedRenderer'          => 'Zend\Mvc\Service\ViewFeedRendererFactory',
        'ViewFeedStrategy'          => 'Zend\Mvc\Service\ViewFeedStrategyFactory',
        'ViewJsonRenderer'          => 'Zend\Mvc\Service\ViewJsonRendererFactory',
        'ViewJsonStrategy'          => 'Zend\Mvc\Service\ViewJsonStrategyFactory',
        /**#@-*/
    );

    /**
     * Aliases
     *
     * @var array
     */
    protected $aliases = array(
        'Zend\EventManager\EventManagerInterface'   => 'EventManager',

        /**#@+
         * From ModuleManagerFactory
         */
        'Config'                                    => 'Configuration',
        'ControllerPluginBroker'                    => 'ControllerPluginManager',
        'Di'                                        => 'DependencyInjector',
        'Zend\Di\LocatorInterface'                  => 'DependencyInjector',
        'Zend\Mvc\Controller\PluginManager'         => 'ControllerPluginManager',
        /**#@-*/
    );
}
