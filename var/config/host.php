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
        'www'       => 'http://pifork.liaowei.com',
        // URI to access uploads directory
        'upload'    => 'http://pifork.liaowei.com/upload',
        // URI to access assets directory
        'asset'     => 'http://pifork.liaowei.com/asset',
        // URI to access static files directory
        'static'    => 'http://pifork.liaowei.com/static',
    ),

    // Paths to resources
    // If path is a relative one then www root path will be prepended
    'path'      => array(
        // WWW root path
        'www'       => '/home/liaowei/project/pifork/www',
        // Library directory
        'lib'       => '/home/liaowei/project/pifork/lib',
        // User extension directory
        'usr'       => '/home/liaowei/project/pifork/usr',
        // User data directory
        'var'       => '/home/liaowei/project/pifork/var',
        // Application module directory
        'module'    => '/home/liaowei/project/pifork/usr/module',
        // Theme directory
        'theme'     => '/home/liaowei/project/pifork/usr/theme',

        // Path to uploads directory
        'upload'    => '/home/liaowei/project/pifork/www/upload',
        // Path to assets directory
        'asset'     => '/home/liaowei/project/pifork/www/asset',
        // Path to static files directory
        'static'    => '/home/liaowei/project/pifork/www/static',

        // Path to vendor library directory
        // Optional, default as lib/vendor
        'vendor'    => '/home/liaowei/project/pifork/lib/vendor',

        // Dependent paths
        // Note: optional, will be located in var if not specified
        // Path to global configuration directory
        'config'    => '/home/liaowei/project/pifork/var/config',
        // Path to cache files directory
        'cache'     => '/home/liaowei/project/pifork/var/cache',
        // Path to logs directory
        'log'       => '/home/liaowei/project/pifork/var/log',
    )
);
