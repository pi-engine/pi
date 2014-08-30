<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * Pi Engine application configurations
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */

return array(
    // ServiceMananger configuration
    'service_manager'   => array(
        // Services that can be instantiated without factories
        'invokables' => array(
            'SharedEventManager'        => 'Zend\EventManager\SharedEventManager',


            // From ServiceListenerFactory
            'DispatchListener'          => 'Zend\Mvc\DispatchListener',
            'RouteListener'             => 'Zend\Mvc\RouteListener',
            //'SendResponseListener'      => 'Zend\Mvc\SendResponseListener',

            // Pi custom service
            'SendResponseListener'      => 'Pi\Mvc\SendResponseListener',
            'ViewHelperManager'         => 'Pi\Mvc\Service\ViewHelperManager',
            'Config'                    => 'Pi\Mvc\Service\Config',
            'ErrorStrategy'             => 'Pi\Mvc\View\Http\ErrorStrategy',
            'ViewStrategyListener'      => 'Pi\Mvc\View\Http\ViewStrategyListener',
            'FeedStrategyListener'      => 'Pi\Mvc\View\Http\FeedStrategyListener',
            'ApiStrategyListener'       => 'Pi\Mvc\View\Http\ApiStrategyListener',
        ),

        // Service factories
        'factories' => array(
            'EventManager'              => 'Zend\Mvc\Service\EventManagerFactory',
            'ModuleManager'             => 'Zend\Mvc\Service\ModuleManagerFactory',

            // From ServiceListenerFactory
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
            //'MvcTranslator'                  => 'Zend\Mvc\Service\TranslatorServiceFactory',
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

            // Pi custom service
            'Application'                   => 'Pi\Mvc\Service\ApplicationFactory',
            'ControllerLoader'              => 'Pi\Mvc\Service\ControllerLoaderFactory',
            'ControllerPluginManager'       => 'Pi\Mvc\Service\ControllerPluginManagerFactory',
            'MvcTranslator'                 => 'Pi\Mvc\Service\TranslatorServiceFactory',
            'ViewResolver'                  => 'Pi\Mvc\Service\ViewResolverFactory',
        ),

        // Aliases
        'aliases' => array(
            'Zend\EventManager\EventManagerInterface'   => 'EventManager',

            // From ServiceListenerFactory
            'Configuration'                          => 'Config',
            'Console'                                => 'ConsoleAdapter',
            'Di'                                     => 'DependencyInjector',
            'Zend\Di\LocatorInterface'               => 'DependencyInjector',
            'Zend\Mvc\Controller\PluginManager'      => 'ControllerPluginManager',
            'Zend\View\Resolver\TemplateMapResolver' => 'ViewTemplateMapResolver',
            'Zend\View\Resolver\TemplatePathStack'   => 'ViewTemplatePathStack',
            'Zend\View\Resolver\AggregateResolver'   => 'ViewResolver',
            'Zend\View\Resolver\ResolverInterface'   => 'ViewResolver',
            'ControllerManager'                      => 'ControllerLoader'
        ),

    ),

    // Listeners to be registered on Application::bootstrap
    'listeners' => array(
        'ViewStrategyListener',
    ),

    // ViewManager configuration
    'view_manager' => array(
        'display_not_found_reason'  => true,
        'display_exceptions'        => true,
        'not_found_template'        => 'error-404',
        'exception_template'        => 'error',
        'error_template'            => 'error',
        'denied_template'           => 'error-denied',
        'layout'                    => 'layout-front',
        'layout_error'              => 'layout-style',
        'layout_ajax'               => 'layout-content',

        'mvc_strategies'            => array(
            'ErrorStrategy',
        ),

        'strategies'                => array(
            'ViewJsonStrategy'
        ),
    ),

    // ViewHelper config placeholder
    'view_helper_config'    => array(),

    // Response sender config
    'send_response' => array(
        // Compression for response
        // By enabling response compression, bandwidth and response time can be decreased but CPU utilization will be increased
        // If compress is needed, it is highly recommended to enable it through web server
        // Or enable `zlib.output_compression` in php.ini
        // @see https://gist.github.com/taiwen/c077ba2c8a33356d8815 for instruction

        // Just in case compression is not enabled by web server or by PHP, specify following specs
        // @note PHP `zlib` extension is required
        'compress'  => false,
    ),
);
