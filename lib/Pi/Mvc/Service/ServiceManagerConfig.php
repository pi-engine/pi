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

use Zend\Mvc\Service\ServiceManagerConfig as ZendServiceManagerConfig;

class ServiceManagerConfig extends ZendServiceManagerConfig
{
    /**
     * Services that can be instantiated without factories
     *
     * @var array
     */
    protected $invokables = array(
        'SharedEventManager'        => 'Zend\EventManager\SharedEventManager',

        /**#@+
         * From ServiceListenerFactory
         */
        'ViewManager'               => 'Pi\Mvc\View\Http\ViewManager',
        'ViewHelperManager'         => 'Pi\Mvc\Service\ViewHelperManager',
        'Config'                    => 'Pi\Mvc\Service\Config',
        'ControllerLoader'          => 'Pi\Mvc\Service\ControllerLoader',
        'ControllerPluginManager'   => 'Pi\Mvc\Controller\PluginManager',
        //'Router'                    => 'Pi\Mvc\Router\Http\TreeRouteStack',

        'DispatchListener'          => 'Zend\Mvc\DispatchListener',
        //'Request'                   => 'Zend\Http\PhpEnvironment\Request',
        //'Response'                  => 'Zend\Http\PhpEnvironment\Response',
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
         * From ServiceListenerFactory
         */
        'Application'               => 'Pi\Mvc\Service\ApplicationFactory',

        'ConsoleAdapter'            => 'Zend\Mvc\Service\ConsoleAdapterFactory',
        //'ConsoleRouter'             => 'Zend\Mvc\Service\RouterFactory',
        'DependencyInjector'        => 'Zend\Mvc\Service\DiFactory',

        //'HttpRouter'                => 'Zend\Mvc\Service\RouterFactory',
        'Request'                   => 'Zend\Mvc\Service\RequestFactory',
        'Response'                  => 'Zend\Mvc\Service\ResponseFactory',
        //'Router'                    => 'Zend\Mvc\Service\RouterFactory',

        //'ViewHelperManager'         => 'Zend\Mvc\Service\ViewHelperManagerFactory',

        'ViewFeedRenderer'          => 'Zend\Mvc\Service\ViewFeedRendererFactory',
        'ViewFeedStrategy'          => 'Zend\Mvc\Service\ViewFeedStrategyFactory',
        'ViewJsonRenderer'          => 'Zend\Mvc\Service\ViewJsonRendererFactory',
        'ViewJsonStrategy'          => 'Zend\Mvc\Service\ViewJsonStrategyFactory',

        'ViewResolver'              => 'Pi\Mvc\Service\ViewResolverFactory',
        'ViewTemplateMapResolver'   => 'Zend\Mvc\Service\ViewTemplateMapResolverFactory',
        'ViewTemplatePathStack'     => 'Zend\Mvc\Service\ViewTemplatePathStackFactory',

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
         * From ServiceListenerFactory
         */
        'Configuration'                             => 'Config',
        'Console'                                   => 'ConsoleAdapter',
        //'ControllerPluginBroker'                    => 'ControllerPluginManager',
        'Di'                                        => 'DependencyInjector',
        'Zend\Di\LocatorInterface'                  => 'DependencyInjector',
        //'Zend\Mvc\Controller\PluginBroker'          => 'ControllerPluginBroker',
        'Zend\Mvc\Controller\PluginManager'         => 'ControllerPluginManager',
        'Zend\View\Resolver\TemplateMapResolver'    => 'ViewTemplateMapResolver',
        'Zend\View\Resolver\TemplatePathStack'      => 'ViewTemplatePathStack',
        'Zend\View\Resolver\AggregateResolver'      => 'ViewResolver',
        'Zend\View\Resolver\ResolverInterface'      => 'ViewResolver',
        /**#@-*/
    );
}
