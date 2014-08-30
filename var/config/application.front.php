<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * Pi Engine standard/front application specifications
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
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
        'i18n'      => array(
            // Translations to be loaded on bootstrap
            'translator'    => array(
                // Global available
                'global'    => array('default'),
                // Module wide
                'module'    => array('default'),
            ),
        ),
        // Module resource, instantiate module service and load module configs
        'module'    => array(),
        // Modules resource, to boot up module bootstraps
        'modules'   => array(),
        // Session resource, load configs from resource.session.php and instantiate session service
        'session'   => array(),
        // Load authentication configs from resource.authentication.php and instantiate authentication service
        'authentication'    => array(),
        // Permission check
        'permission'    => array(
            // Default access perm in case not defined: true for allowed, false for denied
            //'default_allow' => true,
            // Whether to check `site_close` for maintenance, default as true
            //'check_close'   => true,
            // Whether to check page access
            'check_page'    => false,
        ),

        // Instantiate render cache manager
        'render_cache'     => array(
            // Enable etag for browser cache, it is suggested to disable.
            'enable_etag'   => false,
        ),
    ),

    // Service Manager configuration, and Application service configurations managed by Configuration service {@Pi\Mvc\Service\ConfigurationFactory}
    'application'   => include __DIR__ . '/config.application.php',
);
