<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * Pi Engine application configurations
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */

return [
    // ServiceMananger configuration
    'service_manager'    => [
        // Services that can be instantiated without factories
        'invokables' => [
            'SharedEventManager'   => 'Laminas\EventManager\SharedEventManager',

            // From ServiceListenerFactory
            'DispatchListener'     => 'Laminas\Mvc\DispatchListener',
            'RouteListener'        => 'Laminas\Mvc\RouteListener',
            //'SendResponseListener'      => 'Laminas\Mvc\SendResponseListener',
            'ViewJsonRenderer'     => 'Laminas\View\Renderer\JsonRenderer',
            'ViewFeedRenderer'     => 'Laminas\View\Renderer\FeedRenderer',

            // Pi custom service
            'SendResponseListener' => 'Pi\Mvc\SendResponseListener',
            'ViewHelperManager'    => 'Pi\Mvc\Service\ViewHelperManager',
            'Config'               => 'Pi\Mvc\Service\Config',
            'ErrorStrategy'        => 'Pi\Mvc\View\Http\ErrorStrategy',
            'ViewStrategyListener' => 'Pi\Mvc\View\Http\ViewStrategyListener',
            'FeedStrategyListener' => 'Pi\Mvc\View\Http\FeedStrategyListener',
            'ApiStrategyListener'  => 'Pi\Mvc\View\Http\ApiStrategyListener',
        ],

        // Service factories
        'factories'  => [
            'EventManager'                   => 'Laminas\Mvc\Service\EventManagerFactory',
            'ModuleManager'                  => 'Laminas\Mvc\Service\ModuleManagerFactory',

            // From ServiceListenerFactory
            'Application'                    => 'Laminas\Mvc\Service\ApplicationFactory',
            //'Config'                         => 'Laminas\Mvc\Service\ConfigFactory',
            //'ControllerLoader'               => 'Laminas\Mvc\Service\ControllerLoaderFactory',
            //'ControllerPluginManager'        => 'Laminas\Mvc\Service\ControllerPluginManagerFactory',
            'ConsoleAdapter'                 => 'Laminas\Mvc\Service\ConsoleAdapterFactory',
            'ConsoleRouter'                  => 'Laminas\Mvc\Service\RouterFactory',
            'ConsoleViewManager'             => 'Laminas\Mvc\Service\ConsoleViewManagerFactory',
            'DependencyInjector'             => 'Laminas\Mvc\Service\DiFactory',
            'DiAbstractServiceFactory'       => 'Laminas\Mvc\Service\DiAbstractServiceFactoryFactory',
            'DiServiceInitializer'           => 'Laminas\Mvc\Service\DiServiceInitializerFactory',
            'DiStrictAbstractServiceFactory' => 'Laminas\Mvc\Service\DiStrictAbstractServiceFactoryFactory',
            'FilterManager'                  => 'Laminas\Mvc\Service\FilterManagerFactory',
            'FormAnnotationBuilder'          => 'Laminas\Mvc\Service\FormAnnotationBuilderFactory',
            'FormElementManager'             => 'Laminas\Mvc\Service\FormElementManagerFactory',
            'HttpRouter'                     => 'Laminas\Mvc\Service\RouterFactory',
            'HttpMethodListener'             => 'Laminas\Mvc\Service\HttpMethodListenerFactory',
            'HttpViewManager'                => 'Laminas\Mvc\Service\HttpViewManagerFactory',
            'HydratorManager'                => 'Laminas\Mvc\Service\HydratorManagerFactory',
            'InjectTemplateListener'         => 'Laminas\Mvc\Service\InjectTemplateListenerFactory',
            'InputFilterManager'             => 'Laminas\Mvc\Service\InputFilterManagerFactory',
            'LogProcessorManager'            => 'Laminas\Mvc\Service\LogProcessorManagerFactory',
            'LogWriterManager'               => 'Laminas\Mvc\Service\LogWriterManagerFactory',
            //'MvcTranslator'                  => 'Laminas\Mvc\Service\TranslatorServiceFactory',
            'PaginatorPluginManager'         => 'Laminas\Mvc\Service\PaginatorPluginManagerFactory',
            'Request'                        => 'Laminas\Mvc\Service\RequestFactory',
            'Response'                       => 'Laminas\Mvc\Service\ResponseFactory',
            //'Router'                         => 'Laminas\Mvc\Service\RouterFactory',
            'RoutePluginManager'             => 'Laminas\Mvc\Service\RoutePluginManagerFactory',
            'SerializerAdapterManager'       => 'Laminas\Mvc\Service\SerializerAdapterPluginManagerFactory',
            'TranslatorPluginManager'        => 'Laminas\Mvc\Service\TranslatorPluginManagerFactory',
            'ValidatorManager'               => 'Laminas\Mvc\Service\ValidatorManagerFactory',
            //'ViewHelperManager'              => 'Laminas\Mvc\Service\ViewHelperManagerFactory',
            'ViewFeedStrategy'               => 'Laminas\Mvc\Service\ViewFeedStrategyFactory',
            'ViewJsonStrategy'               => 'Laminas\Mvc\Service\ViewJsonStrategyFactory',
            'ViewManager'                    => 'Laminas\Mvc\Service\ViewManagerFactory',
            'ViewResolver'                   => 'Laminas\Mvc\Service\ViewResolverFactory',
            'ViewTemplateMapResolver'        => 'Laminas\Mvc\Service\ViewTemplateMapResolverFactory',
            'ViewTemplatePathStack'          => 'Laminas\Mvc\Service\ViewTemplatePathStackFactory',
            'ViewPrefixPathStackResolver'    => 'Laminas\Mvc\Service\ViewPrefixPathStackResolverFactory',

            // Pi custom service
            'Application'                    => 'Pi\Mvc\Service\ApplicationFactory',
            'ControllerLoader'               => 'Pi\Mvc\Service\ControllerLoaderFactory',
            'ControllerPluginManager'        => 'Pi\Mvc\Service\ControllerPluginManagerFactory',
            'MvcTranslator'                  => 'Pi\Mvc\Service\TranslatorServiceFactory',
            'ViewResolver'                   => 'Pi\Mvc\Service\ViewResolverFactory',
        ],

        // Aliases
        'aliases'    => [
            'Laminas\EventManager\EventManagerInterface'    => 'EventManager',
            'Laminas\Mvc\View\Http\InjectTemplateListener'  => 'InjectTemplateListener',

            // From ServiceListenerFactory
            'Configuration'                              => 'Config',
            'Console'                                    => 'ConsoleAdapter',
            'Di'                                         => 'DependencyInjector',
            'Laminas\Di\LocatorInterface'                   => 'DependencyInjector',
            'Laminas\Form\Annotation\FormAnnotationBuilder' => 'FormAnnotationBuilder',
            'Laminas\Mvc\Controller\PluginManager'          => 'ControllerPluginManager',
            'Laminas\Mvc\View\Http\InjectTemplateListener'  => 'InjectTemplateListener',
            'Laminas\View\Resolver\TemplateMapResolver'     => 'ViewTemplateMapResolver',
            'Laminas\View\Resolver\TemplatePathStack'       => 'ViewTemplatePathStack',
            'Laminas\View\Resolver\AggregateResolver'       => 'ViewResolver',
            'Laminas\View\Resolver\ResolverInterface'       => 'ViewResolver',
            'ControllerManager'                          => 'ControllerLoader',
        ],

    ],

    // Listeners to be registered on Application::bootstrap
    'listeners'          => [
        'ViewStrategyListener',
    ],

    // ViewManager configuration
    'view_manager'       => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'not_found_template'       => 'error-404',
        'exception_template'       => 'error',
        'error_template'           => 'error',
        'denied_template'          => 'error-denied',
        'layout'                   => 'layout-front',
        'layout_error'             => 'layout-simple',
        'layout_ajax'              => 'layout-content',

        'mvc_strategies' => [
            'ErrorStrategy',
        ],

        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],

    // ViewHelper config placeholder
    'view_helper_config' => [],

    // Response sender config
    'send_response'      => [
        // Compression for response
        // By enabling response compression, bandwidth and response time can be decreased but CPU utilization will be increased
        // If compress is needed, it is highly recommended to enable it through web server
        // Or enable `zlib.output_compression` in php.ini
        // @see https://gist.github.com/taiwen/c077ba2c8a33356d8815 for instruction

        // Just in case compression is not enabled by web server or by PHP, specify following specs
        // @note PHP `zlib` extension is required
        'compress' => false,
    ],
];