<?php
/**
 * Pi Engine application specifications
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
 * @version         $Id$
 */

return array(
    // Global configs, to be loaded to general config
    'config'    => array(),

    // System application services to be loaded on bootstrap
    'service'   => array(
        // Log service
        'log'   => array(),
    ),

    //Bootstrap resources
    // resource key => resource options (array) or resource config file (string)
    'resource'  => array(
        // Security resource, load configs from resource.security.php
        'security'  => 'security',
        // DB connection resource, load configs from resource.db.php
        'database'  => array(),
        // Config resource to load configs from DB
        'config'    => array(),
        // MVC router resource, load routes from DB
        'router'    => array(
            //'class'     => 'Pi\\Mvc\\Router\\RouteStack',
        ),
        // Intl resource, instantiate translator services and load specified translation data
        'i18n'      => 'i18n',
        // Module resource, instantiate module service and load module configs
        'module'    => array(),
        // Modules resource, to boot up module bootstraps
        'modules'   => array(),
        // Session resource, load configs from resource.session.php and instantiate session service
        'session'   => array(),
        // Load authentication configs from resource.authentication.php and instantiate authentication service
        'authentication'    => array(),
        // Instantiate use handler
        'user'      => array(),
        // Instantiate ACL manager and register listeners
        'acl'       => array(
            // Default access perm in case not defined
            'default'       => true,
            // If check page access
            'check_page'    => false,
        ),

        // Instantiate render cache manager
        'render'     => array(
            // Enable page caching, default as false
            'page'      => true,
            // Enable action caching, default as false
            'action'    => false,
        ),
    ),

    /**#@+
     * Service Manager configuration, and Application service configurations managed by Configuration service {@Pi\Mvc\Service\ConfigurationFactory}
     */
    // Application service configuration
    'application'   => array(
        // ServiceMananger configuration
        'service_manager'   => array(
            // Services that can be instantiated without factories
            'invokables' => array(
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
                'PrepareViewModel'          => 'Pi\Mvc\View\Http\PrepareViewModelListener',
                'PrepareFeedModel'          => 'Pi\Mvc\View\Http\PrepareFeedModelListener',
                //'ViewManager'               => 'Pi\Mvc\View\Http\ViewManager',
                'ViewHelperManager'         => 'Pi\Mvc\Service\ViewHelperManager',
                'Config'                    => 'Pi\Mvc\Service\Config',
                'DeniedRendering'           => 'Pi\Mvc\View\Http\DeniedStrategy',
                'ThemeRendering'            => 'Pi\Mvc\View\Http\ThemeRenderingStrategy',
                /**#@-*/
            ),

            // Service factories
            'factories' => array(
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
                'HttpViewManager'                => 'Zend\Mvc\Service\HttpViewManagerFactory',
                'HydratorManager'                => 'Zend\Mvc\Service\HydratorManagerFactory',
                'InputFilterManager'             => 'Zend\Mvc\Service\InputFilterManagerFactory',
                'MvcTranslator'                  => 'Zend\Mvc\Service\TranslatorServiceFactory',
                'PaginatorPluginManager'         => 'Zend\Mvc\Service\PaginatorPluginManagerFactory',
                'Request'                        => 'Zend\Mvc\Service\RequestFactory',
                'Response'                       => 'Zend\Mvc\Service\ResponseFactory',
                //'Router'                         => 'Zend\Mvc\Service\RouterFactory',
                'RoutePluginManager'             => 'Zend\Mvc\Service\RoutePluginManagerFactory',
                'SerializerAdapterManager'       => 'Zend\Mvc\Service\SerializerAdapterPluginManagerFactory',
                'ValidatorManager'               => 'Zend\Mvc\Service\ValidatorManagerFactory',
                //'ViewHelperManager'              => 'Zend\Mvc\Service\ViewHelperManagerFactory',
                'ViewFeedRenderer'               => 'Zend\Mvc\Service\ViewFeedRendererFactory',
                'ViewFeedStrategy'               => 'Zend\Mvc\Service\ViewFeedStrategyFactory',
                'ViewJsonRenderer'               => 'Zend\Mvc\Service\ViewJsonRendererFactory',
                'ViewJsonStrategy'               => 'Zend\Mvc\Service\ViewJsonStrategyFactory',
                'ViewManager'                    => 'Zend\Mvc\Service\ViewManagerFactory',
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
            ),

            // Aliases
            'aliases' => array(
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
            ),

        ),

        // Listeners to be registered on Application::bootstrap
        'listeners' => array(
            'PrepareViewModel',
        ),

        // ViewManager configuration
        'view_manager' => array(
            'display_not_found_reason'  => true,
            'display_exceptions'        => true,
            'not_found_template'        => 'error-404',
            'exception_template'        => 'error-exception',
            'denied_template'           => 'error-denied',
            'layout'                    => 'layout-front',
            'layout_error'              => 'layout-style',
            'layout_ajax'               => 'layout-conent',

            'mvc_strategies'            => array(
                'DeniedRendering',
                //'PrepareViewModel',
                'ThemeRendering',
            ),

            'strategies'                => array(
                'ViewJsonStrategy'
            ),
        ),

        'send_response' => array(
            // Compress for response
            // @see Zend\Filter\Compress\Gz
            'compress'  => array(
                'mode'      => false,   // Valid modes: deflate, gzip; default as 'gzip', false for disable
                'level'     => 6,
                //'archive'   => null,
            ),
        ),
    )
    /**#@-*/
);
