<?php
/**
 * Pi Engine host specifications
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

//Host definition file
//Paths/URLs to system folders
//URIs without a leading slash are considered relative to the current Pi Engine host location
//URIs with a leading slash are considered semi-relative (you must setup proper rewriting rules in your server conf)

return array(
    // URIs to resources
    // If URI is a relative one then www root URI will be prepended
    'uri'       => array(
        // WWW root URI
        'www'       => 'http://siteurl.tld',
        // URI to access uploads directory
        'upload'    => 'http://siteurl.tld/upload',
        // URI to access assets directory
        'asset'     => 'http://siteurl.tld/asset',
        // URI to access static files directory
        'static'    => 'http://siteurl.tld/static',
    ),

    // Paths to resources
    // If path is a relative one then www root path will be prepended
    'path'      => array(
        // WWW root path
        //'www'       => '/path/to/www',
        // Library directory
        //'lib'       => '/path/to/lib',
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
        // Note: optional, will be located in var if not specified
        // Path to global configuration directory
        'config'    => '/path/to/var/config',
        // Path to cache files directory
        'cache'     => '/path/to/var/cache',
        // Path to logs directory
        'log'       => '/path/to/var/log',
    )
);
