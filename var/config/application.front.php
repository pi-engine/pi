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
        'i18n'      => array(
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
    //ServiceMananger Configuration
    'service_manager'   => array(
    ),
    // Application service configuration
    'application'   => array(
        'view_manager' => array(
            'display_not_found_reason'  => true,
            'display_exceptions'        => true,
            'not_found_template'        => 'error-404',
            'exception_template'        => 'error-exception',
            'denied_template'           => 'error-denied',
            'layout_error'              => 'layout-style',
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
