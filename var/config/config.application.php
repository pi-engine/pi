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
        'aliases'            => [
            'configuration'                              => 'config',
            'Configuration'                              => 'config',
            'console'                                    => 'ConsoleAdapter',
            'Console'                                    => 'ConsoleAdapter',
            'ConsoleDefaultRenderingStrategy'            => Zend\Mvc\View\Console\DefaultRenderingStrategy::class,
            'ControllerLoader'                           => 'ControllerManager',
            'Di'                                         => 'DependencyInjector',
            'HttpDefaultRenderingStrategy'               => Zend\Mvc\View\Http\DefaultRenderingStrategy::class,
            //'MiddlewareListener'                       => 'Zend\Mvc\MiddlewareListener',
            //'RouteListener'                            => 'Zend\Mvc\RouteListener',
            //'SendResponseListener'                     => 'Zend\Mvc\SendResponseListener',
            'View'                                       => 'Zend\View\View',
            'ViewFeedRenderer'                           => 'Zend\View\Renderer\FeedRenderer',
            'ViewJsonRenderer'                           => 'Zend\View\Renderer\JsonRenderer',
            'ViewPhpRendererStrategy'                    => 'Zend\View\Strategy\PhpRendererStrategy',
            'ViewPhpRenderer'                            => 'Zend\View\Renderer\PhpRenderer',
            'ViewRenderer'                               => 'Zend\View\Renderer\PhpRenderer',
            'Zend\Di\LocatorInterface'                   => 'DependencyInjector',
            'Zend\Form\Annotation\FormAnnotationBuilder' => 'FormAnnotationBuilder',
            'Zend\Mvc\Controller\PluginManager'          => 'ControllerPluginManager',
            'Zend\Mvc\View\Http\InjectTemplateListener'  => 'InjectTemplateListener',
            'Zend\View\Renderer\RendererInterface'       => 'Zend\View\Renderer\PhpRenderer',
            'Zend\View\Resolver\TemplateMapResolver'     => 'ViewTemplateMapResolver',
            'Zend\View\Resolver\TemplatePathStack'       => 'ViewTemplatePathStack',
            'Zend\View\Resolver\AggregateResolver'       => 'ViewResolver',
            'Zend\View\Resolver\ResolverInterface'       => 'ViewResolver',
        ],
        'invokables'         => [
            'SharedEventManager'   => 'Zend\EventManager\SharedEventManager',

            // From ServiceListenerFactory
            //'DispatchListener'   => 'Zend\Mvc\DispatchListener',
            'RouteListener'        => 'Zend\Mvc\RouteListener',
            'MiddlewareListener'   => 'Zend\Mvc\MiddlewareListener',
            'ViewJsonRenderer'     => 'Zend\View\Renderer\JsonRenderer',
            'ViewFeedRenderer'     => 'Zend\View\Renderer\FeedRenderer',

            // Pi custom service
            'SendResponseListener' => 'Pi\Mvc\SendResponseListener',
            'ViewHelperManager'    => 'Pi\Mvc\Service\ViewHelperManager',
            'Config'               => 'Pi\Mvc\Service\Config',
            'ErrorStrategy'        => 'Pi\Mvc\View\Http\ErrorStrategy',
            'ViewStrategyListener' => 'Pi\Mvc\View\Http\ViewStrategyListener',
            'FeedStrategyListener' => 'Pi\Mvc\View\Http\FeedStrategyListener',
            'ApiStrategyListener'  => 'Pi\Mvc\View\Http\ApiStrategyListener',
        ],
        'factories'          => [
            'Application'                                         => Zend\Mvc\Service\ApplicationFactory::class,
            //'config'                                            => 'Zend\Mvc\Service\ConfigFactory',
            'ControllerManager'                                   => 'Zend\Mvc\Service\ControllerManagerFactory',
            //'ControllerPluginManager'                           => 'Zend\Mvc\Service\ControllerPluginManagerFactory',
            'ConsoleAdapter'                                      => 'Zend\Mvc\Service\ConsoleAdapterFactory',
            'ConsoleExceptionStrategy'                            => Zend\Mvc\Service\ConsoleExceptionStrategyFactory::class,
            'ConsoleRouter'                                       => Zend\Mvc\Service\ConsoleRouterFactory::class,
            'ConsoleRouteNotFoundStrategy'                        => Zend\Mvc\Service\ConsoleRouteNotFoundStrategyFactory::class,
            'ConsoleViewManager'                                  => 'Zend\Mvc\Service\ConsoleViewManagerFactory',
            'DependencyInjector'                                  => Zend\Mvc\Service\DiFactory::class,
            'DiAbstractServiceFactory'                            => Zend\Mvc\Service\DiAbstractServiceFactoryFactory::class,
            'DiServiceInitializer'                                => Zend\Mvc\Service\DiServiceInitializerFactory::class,
            'DiStrictAbstractServiceFactory'                      => Zend\Mvc\Service\DiStrictAbstractServiceFactoryFactory::class,
            'DispatchListener'                                    => 'Zend\Mvc\Service\DispatchListenerFactory',
            'FilterManager'                                       => 'Zend\Mvc\Service\FilterManagerFactory',
            'FormAnnotationBuilder'                               => 'Zend\Mvc\Service\FormAnnotationBuilderFactory',
            'FormElementManager'                                  => 'Zend\Mvc\Service\FormElementManagerFactory',
            'HttpExceptionStrategy'                               => Zend\Mvc\Service\HttpExceptionStrategyFactory::class,
            'HttpMethodListener'                                  => 'Zend\Mvc\Service\HttpMethodListenerFactory',
            'HttpRouteNotFoundStrategy'                           => Zend\Mvc\Service\HttpRouteNotFoundStrategyFactory::class,
            'HttpRouter'                                          => Zend\Mvc\Service\HttpRouterFactory::class,
            'HttpViewManager'                                     => 'Zend\Mvc\Service\HttpViewManagerFactory',
            'HydratorManager'                                     => 'Zend\Mvc\Service\HydratorManagerFactory',
            'InjectTemplateListener'                              => 'Zend\Mvc\Service\InjectTemplateListenerFactory',
            'InputFilterManager'                                  => 'Zend\Mvc\Service\InputFilterManagerFactory',
            'LogProcessorManager'                                 => 'Zend\Mvc\Service\LogProcessorManagerFactory',
            'LogWriterManager'                                    => 'Zend\Mvc\Service\LogWriterManagerFactory',
            //'MvcTranslator'                                     => 'Zend\Mvc\Service\TranslatorServiceFactory',
            'PaginatorPluginManager'                              => 'Zend\Mvc\Service\PaginatorPluginManagerFactory',
            'Request'                                             => 'Zend\Mvc\Service\RequestFactory',
            'Response'                                            => 'Zend\Mvc\Service\ResponseFactory',
            //'Router'                                            => 'Zend\Mvc\Service\RouterFactory',
            'RoutePluginManager'                                  => 'Zend\Mvc\Service\RoutePluginManagerFactory',
            'SerializerAdapterManager'                            => 'Zend\Mvc\Service\SerializerAdapterPluginManagerFactory',
            'TranslatorPluginManager'                             => 'Zend\Mvc\Service\TranslatorPluginManagerFactory',
            'ValidatorManager'                                    => 'Zend\Mvc\Service\ValidatorManagerFactory',
            Zend\Mvc\View\Console\DefaultRenderingStrategy::class => Zend\ServiceManager\Factory\InvokableFactory::class,
            //'ViewHelperManager'                                 => 'Zend\Mvc\Service\ViewHelperManagerFactory',
            Zend\Mvc\View\Http\DefaultRenderingStrategy::class    => Zend\Mvc\Service\HttpDefaultRenderingStrategyFactory::class,
            'ViewFeedStrategy'                                    => 'Zend\Mvc\Service\ViewFeedStrategyFactory',
            'ViewJsonStrategy'                                    => 'Zend\Mvc\Service\ViewJsonStrategyFactory',
            'ViewManager'                                         => 'Zend\Mvc\Service\ViewManagerFactory',
            //'ViewResolver'                                      => 'Zend\Mvc\Service\ViewResolverFactory',
            'ViewTemplateMapResolver'                             => 'Zend\Mvc\Service\ViewTemplateMapResolverFactory',
            'ViewTemplatePathStack'                               => 'Zend\Mvc\Service\ViewTemplatePathStackFactory',
            'ViewPrefixPathStackResolver'                         => 'Zend\Mvc\Service\ViewPrefixPathStackResolverFactory',
            'Zend\Mvc\MiddlewareListener'                         => Zend\ServiceManager\Factory\InvokableFactory::class,
            'Zend\Mvc\RouteListener'                              => Zend\ServiceManager\Factory\InvokableFactory::class,
            'Zend\Mvc\SendResponseListener'                       => Zend\ServiceManager\Factory\InvokableFactory::class,
            'Zend\View\Renderer\FeedRenderer'                     => Zend\ServiceManager\Factory\InvokableFactory::class,
            'Zend\View\Renderer\JsonRenderer'                     => Zend\ServiceManager\Factory\InvokableFactory::class,
            'Zend\View\Renderer\PhpRenderer'                      => Zend\Mvc\Service\ViewPhpRendererFactory::class,
            'Zend\View\Strategy\PhpRendererStrategy'              => Zend\Mvc\Service\ViewPhpRendererStrategyFactory::class,
            'Zend\View\View'                                      => Zend\Mvc\Service\ViewFactory::class,

            // Pi custom service
            'Application'                                         => 'Pi\Mvc\Service\ApplicationFactory',
            'ControllerLoader'                                    => 'Pi\Mvc\Service\ControllerLoaderFactory',
            'ControllerPluginManager'                             => 'Pi\Mvc\Service\ControllerPluginManagerFactory',
            'MvcTranslator'                                       => 'Pi\Mvc\Service\TranslatorServiceFactory',
            'ViewResolver'                                        => 'Pi\Mvc\Service\ViewResolverFactory',
        ],
        'abstract_factories' => [
            'Zend\Form\FormAbstractServiceFactory',
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
