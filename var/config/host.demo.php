<?php
/**
 * Pi Engine demo host specifications
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
        'www'       => 'http://pi-demo',
        // URI to access uploads directory
        'upload'    => 'http://pi-demo/upload',
        // URI to access assets directory
        'asset'     => 'http://pi-demo/asset',
        // URI to access static files directory
        'static'    => 'http://pi-demo/static',
    ),

    // Paths to resources
    // If path is a relative one then www root path will be prepended
    'path'      => array(
        // User extension directory
        'usr'       => '/path/to/pi-demo/usr',
        // Application module directory
        'module'    => '/path/to/pi-demo/usr/module',
        // Theme directory
        'theme'     => '/path/to/pi-demo/usr/theme',
        // Path to vendor library directory
        'vendor'    => '/path/to/pi-demo/lib/vendor',

        // Path to uploads directory
        'upload'    => '/path/to/pi-demo-demo/upload',
        // Path to assets directory
        'asset'     => '/path/to/pi-demo-demo/asset',
        // Path to static files directory
        'static'    => '/path/to/pi-demo-demo/static',

        // User data directory
        'var'       => '/path/to/pi-demo-demo/var',
        // Path to global configuration directory
        'config'    => '/path/to/pi-demo-demo/config',
        // Path to cache files directory
        'cache'     => '/path/to/pi-demo-demo/cache',
        // Path to logs directory
        'log'       => '/path/to/pi-demo-demo/log',
    )
);
