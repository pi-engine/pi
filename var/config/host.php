<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * Pi Engine host specifications
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */

/**
 * Host definition file
 *
 * Paths/URLs to system folders
 *
 * - URIs without a leading slash are considered relative
 *      to the current Pi Engine host location
 * - URIs with a leading slash are considered semi-relative
 *      requires proper rewriting rules in server conf
 */
return array(
    // URIs to resources
    // If URI is a relative one then www root URI will be prepended
    'uri'       => array(
        // WWW root URI
        'www'       => 'http://pi-engine.org',
        // URI to access uploads directory
        'upload'    => 'http://pi-engine.org/upload',
        // URI to access assets directory
        'asset'     => 'http://pi-engine.org/asset',
        // URI to access static files directory
        'static'    => 'http://pi-engine.org/static',
    ),

    // Paths to resources
    // If path is a relative one then www root path will be prepended
    'path'      => array(
        // WWW root path
        'www'       => '/path/to/www',
        // Library directory
        'lib'       => '/path/to/lib',
        // User extension directory
        'usr'       => '/path/to/usr',
        // User data directory
        'var'       => '/path/to/var',
        // Application module directory
        'module'    => '/path/to/usr/module',
        // Theme directory
        'theme'     => '/path/to/usr/theme',

        // Path to uploads directory
        'upload'    => '/path/to/www/upload',
        // Path to assets directory
        'asset'     => '/path/to/www/asset',
        // Path to static files directory
        'static'    => '/path/to/www/static',

        // Path to vendor library directory
        // Optional, default as lib/vendor
        'vendor'    => '/path/to/lib/vendor',

        // Dependent paths
        // Path to global configuration directory
        'config'    => '/path/to/var/config',
        // Path to custom configuration directory
        'custom'    => '/path/to/var/custom',
        // Path to cache files directory
        'cache'     => '/path/to/var/cache',
        // Path to logs directory
        'log'       => '/path/to/var/log',
    )
);
