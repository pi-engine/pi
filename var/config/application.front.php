<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * Pi Engine standard/front application specifications
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
                'global'    => array('usr:main'),
                // Module wide
                'module'    => array('main'),
            )
        ),
        // Module resource, instantiate module service and load module configs
        'module'    => array(),
        // Modules resource, to boot up module bootstraps
        'modules'   => array(),
        // Session resource, load configs from resource.session.php and instantiate session service
        'session'   => array(),
        // Load authentication configs from resource.authentication.php and instantiate authentication service
        //'authentication'    => array(),
        // Instantiate use handler
        'user'      => array(),
        // Instantiate ACL manager and register listeners

        'acl'       => array(
            // Default access perm in case not defined: true for allowed, false for denied
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
    'application'   => include __DIR__ . '/config.application.php',
    /**#@-*/
);
