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
        'DispatchListener'          => 'Zend\Mvc\DispatchListener',
        'RouteListener'             => 'Zend\Mvc\RouteListener',
        'SendResponseListener'      => 'Zend\Mvc\SendResponseListener',
        /**#@-*/

        /**#@+
         * Pi custom service
         */
        'ViewManager'               => 'Pi\Mvc\View\Http\ViewManager',
        'ViewHelperManager'         => 'Pi\Mvc\Service\ViewHelperManager',
        'Config'                    => 'Pi\Mvc\Service\Config',
        /**#@-*/
    );

    /**
     * Service factories
     *
     * @var array
     */
    protected $factories = array(
        'EventManager'              => 'Zend\Mvc\Service\EventManagerFactory',
        'ModuleManager'             => 'Zend\Mvc\Service\ModuleManagerFactory',

        /**#@+
         * From ServiceListenerFactory
         */
        'Application'                    => 'Zend\Mvc\Service\ApplicationFactory',
        //'Config'                         => 'Zend\Mvc\Service\ConfigFactory',
        //'ControllerLoader'               => 'Zend\Mvc\Service\ControllerLoaderFactory',
        //'ControllerPluginManager'        => 'Zend\Mvc\Service\ControllerPluginManagerFactory',
        'ConsoleAdapter'                 => 'Zend\Mvc\Service\ConsoleAdapterFactory',
        'ConsoleRouter'                  => 'Zend\Mvc\Service\RouterFactory',
        'DependencyInjector'             => 'Zend\Mvc\Service\DiFactory',
        'DiAbstractServiceFactory'       => 'Zend\Mvc\Service\DiAbstractServiceFactoryFactory',
        'DiServiceInitializer'           => 'Zend\Mvc\Service\DiServiceInitializerFactory',
        'DiStrictAbstractServiceFactory' => 'Zend\Mvc\Service\DiStrictAbstractServiceFactoryFactory',
        'FilterManager'                  => 'Zend\Mvc\Service\FilterManagerFactory',
        'FormElementManager'             => 'Zend\Mvc\Service\FormElementManagerFactory',
        'HttpRouter'                     => 'Zend\Mvc\Service\RouterFactory',
        'PaginatorPluginManager'         => 'Zend\Mvc\Service\PaginatorPluginManagerFactory',
        'Request'                        => 'Zend\Mvc\Service\RequestFactory',
        'Response'                       => 'Zend\Mvc\Service\ResponseFactory',
        //'Router'                         => 'Zend\Mvc\Service\RouterFactory',
        'RoutePluginManager'             => 'Zend\Mvc\Service\RoutePluginManagerFactory',
        'ValidatorManager'               => 'Zend\Mvc\Service\ValidatorManagerFactory',
        //'ViewHelperManager'              => 'Zend\Mvc\Service\ViewHelperManagerFactory',
        'ViewFeedRenderer'               => 'Zend\Mvc\Service\ViewFeedRendererFactory',
        'ViewFeedStrategy'               => 'Zend\Mvc\Service\ViewFeedStrategyFactory',
        'ViewJsonRenderer'               => 'Zend\Mvc\Service\ViewJsonRendererFactory',
        'ViewJsonStrategy'               => 'Zend\Mvc\Service\ViewJsonStrategyFactory',
        //'ViewManager'                    => 'Zend\Mvc\Service\ViewManagerFactory',
        'ViewResolver'                   => 'Zend\Mvc\Service\ViewResolverFactory',
        'ViewTemplateMapResolver'        => 'Zend\Mvc\Service\ViewTemplateMapResolverFactory',
        'ViewTemplatePathStack'          => 'Zend\Mvc\Service\ViewTemplatePathStackFactory',
        /**#@-*/

        /**#@+
         * Pi custom service
         */
        'Application'                   => 'Pi\Mvc\Service\ApplicationFactory',
        'ControllerLoader'              => 'Pi\Mvc\Service\ControllerLoaderFactory',
        'ControllerPluginManager'       => 'Pi\Mvc\Service\ControllerPluginManagerFactory',
        'ViewResolver'                  => 'Pi\Mvc\Service\ViewResolverFactory',
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
        'Configuration'                          => 'Config',
        'Console'                                => 'ConsoleAdapter',
        'Di'                                     => 'DependencyInjector',
        'Zend\Di\LocatorInterface'               => 'DependencyInjector',
        'Zend\Mvc\Controller\PluginManager'      => 'ControllerPluginManager',
        'Zend\View\Resolver\TemplateMapResolver' => 'ViewTemplateMapResolver',
        'Zend\View\Resolver\TemplatePathStack'   => 'ViewTemplatePathStack',
        'Zend\View\Resolver\AggregateResolver'   => 'ViewResolver',
        'Zend\View\Resolver\ResolverInterface'   => 'ViewResolver',
        /**#@-*/
    );
}
