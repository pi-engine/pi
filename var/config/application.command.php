<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * Pi Engine standard/front application specifications
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */

/**
 * For more info, @see application.front.php
 */
return [
    'config'      => [],
    'service'     => [],
    'resource'    => [
        'database' => [],
        'config'   => [],
        'i18n'     => [
            'translator' => [
                'global' => ['default'],
                'module' => ['default'],
            ],
        ],
        // Module resource, instantiate module service and load module configs
        'module'   => [],
        // Modules resource, to boot up module bootstraps
        'modules'  => [],
    ],

    // Service Manager configuration, and Application service configurations managed by Configuration service {@Pi\Mvc\Service\ConfigurationFactory}
    'application' => [
        // ServiceMananger configuration
        'service_manager'    => [
            // Services that can be instantiated without factories
            'invokables' => [
                'SharedEventManager'   => 'Laminas\EventManager\SharedEventManager',


                // From ServiceListenerFactory
                'DispatchListener'     => 'Laminas\Mvc\DispatchListener',
                'RouteListener'        => 'Pi\Command\Mvc\RouteListener',
                'SendResponseListener' => 'Laminas\Mvc\SendResponseListener',

                // Pi custom service
                'Config'               => 'Pi\Mvc\Service\Config',
                'ViewStrategyListener' => 'Pi\Mvc\View\Http\ViewStrategyListener',
                'ConsoleViewManager'   => 'Laminas\Mvc\View\Console\ViewManager',
            ],

            // Service factories
            'factories'  => [
                'EventManager'            => 'Laminas\Mvc\Service\EventManagerFactory',
                'ModuleManager'           => 'Laminas\Mvc\Service\ModuleManagerFactory',

                // From ServiceListenerFactory
                'ConsoleAdapter'          => 'Laminas\Mvc\Service\ConsoleAdapterFactory',
                'ConsoleRouter'           => 'Pi\Command\Mvc\Service\RouterFactory',
                'Request'                 => 'Laminas\Mvc\Service\RequestFactory',
                'Response'                => 'Laminas\Mvc\Service\ResponseFactory',
                'Router'                  => 'Pi\Command\Mvc\Service\RouterFactory',
                'RoutePluginManager'      => 'Laminas\Mvc\Service\RoutePluginManagerFactory',
                'ViewManager'             => 'Laminas\Mvc\Service\ViewManagerFactory',

                // Pi custom service
                'Application'             => 'Pi\Command\Mvc\Service\ApplicationFactory',
                'ControllerLoader'        => 'Pi\Mvc\Service\ControllerLoaderFactory',
                'ControllerPluginManager' => 'Pi\Mvc\Service\ControllerPluginManagerFactory',
            ],

            // Aliases
            'aliases'    => [
                'Laminas\EventManager\EventManagerInterface' => 'EventManager',

                // From ServiceListenerFactory
                'Configuration'                           => 'Config',
                'Console'                                 => 'ConsoleAdapter',
                'Laminas\Mvc\Controller\PluginManager'       => 'ControllerPluginManager',
                'ControllerManager'                       => 'ControllerLoader',
            ],

        ],

        // Listeners to be registered on Application::bootstrap
        'listeners'          => [],

        // ViewManager configuration
        'view_manager'       => [
            'display_not_found_reason' => true,
            'display_exceptions'       => true,
        ],

        // ViewHelper config placeholder
        'view_helper_config' => [],

        // Response sender config
        'send_response'      => [],
    ],
];
