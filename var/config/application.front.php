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

return [
    // Global configs, to be loaded to general config
    'config'      => [],

    // System application services to be loaded on bootstrap
    'service'     => [
        // Log service
        'log' => [],
    ],

    //Bootstrap resources
    // resource key => resource options (array) or resource config file (string)
    'resource'    => [
        // Security resource, load configs from resource.security.php
        'security'       => 'security',
        // DB connection resource, load configs from resource.db.php
        'database'       => [],
        // Config resource to load configs from DB
        'config'         => [],
        // MVC router resource, load routes from DB
        'router'         => [
            //'class'     => 'Pi\\Mvc\\Router\\RouteStack',
        ],
        // Intl resource, instantiate translator services and load specified translation data
        'i18n'           => [
            // Translations to be loaded on bootstrap
            'translator' => [
                // Global available
                'global' => ['default'],
                // Module wide
                'module' => ['default'],
            ],
        ],
        // Module resource, instantiate module service and load module configs
        'module'         => [],
        // Modules resource, to boot up module bootstraps
        'modules'        => [],
        // Session resource, load configs from resource.session.php and instantiate session service
        'session'        => [],
        // Load authentication configs from resource.authentication.php and instantiate authentication service
        'authentication' => [],
        // Permission check
        'permission'     => [
            // Default access perm in case not defined: true for allowed, false for denied
            //'default_allow' => true,
            // Whether to check `site_close` for maintenance, default as true
            //'check_close'   => true,
            // Whether to check page access
            'check_page' => true,
        ],

        // Instantiate render cache manager
        'render_cache'   => [
            // Enable etag for browser cache, it is suggested to disable.
            'enable_etag' => false,
        ],
    ],

    // Service Manager configuration, and Application service configurations managed by Configuration service {@Pi\Mvc\Service\ConfigurationFactory}
    'application' => include __DIR__ . '/config.application.php',
];
