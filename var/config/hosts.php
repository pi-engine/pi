<?php
/**
 * Pi Engine multi-host specifications
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
    // Alias of hosts
    'alias' => array(
        'http://pi'                 => 'default',
        'http://www.pi'             => 'default',
        'http://localhost/pi/www'   => 'default',
        'http://pi-demo'            => 'demo',
        'http://www.pi-demo'        => 'demo',
    ),
    // Host specifications
    'hosts' => array(
        // Specifications defined by files
        'default'   => __DIR__ . '/host.php',
        'demo'      => __DIR__ . '/host.demo.php',
        /*
        // Specifications defined instantly
        'default'       => array(
            // URIs to resources
            'uri'       => array(
                'www'       => 'http://pi',
                'upload'    => 'http://pi/upload',
                'asset'     => 'http://pi/asset',
                'static'    => 'http://pi/static',
            ),
            // Paths to resources
            'path'      => array(
                'usr'       => '/path/to/pi/usr',
                'var'       => '/path/to/pi/var',
                'module'    => '/path/to/pi/usr/module',
                'theme'     => '/path/to/pi/usr/theme',
                'upload'    => '/path/to/pi/www/upload',
                'asset'     => '/path/to/pi/www/asset',
                'static'    => '/path/to/pi/www/static',
                'vendor'    => '/path/to/pi/lib/vendor',
                'config'    => '/path/to/pi/var/config',
                'cache'     => '/path/to/pi/var/cache',
                'log'       => '/path/to/pi/var/log',
            ),
        ),
        */
    ),
);
