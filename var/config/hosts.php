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
 */

return array(
    // Alias of hosts
    // Root URL => identifier of specifications
    'alias' => array(
        'http://pi-engine.org'      => 'default',
        'http://www.pi-engine.org'  => 'default',
        'http://pi-demo.org'        => 'demo',
        'http://www.pi-demo.org'    => 'demo',
        'http://pialog.org'         => 'org',
        'http://www.pialog.org'     => 'org',
    ),
    // Host specifications
    'hosts' => array(
        // Specifications defined by files
        'demo'      => __DIR__ . '/host.demo.php',
        'org'       => '/path/to/org/config/host.php',

        // Specifications defined instantly
        'default'       => array(
            // URIs to resources
            'uri'       => array(
                'www'       => 'http://pi-engine.org',
                'upload'    => 'http://pi-engine.org/upload',
                'asset'     => 'http://pi-engine.org/asset',
                'static'    => 'http://pi-engine.org/static',
            ),
            // Paths to resources
            'path'      => array(
                'usr'       => '/path/to/pi/usr',
                'var'       => '/path/to/pi/var',
                'module'    => '/path/to/pi/usr/module',
                'theme'     => '/path/to/pi/usr/theme',

                'upload'    => '/path/to/pi/upload',
                'asset'     => '/path/to/pi/asset',
                'static'    => '/path/to/pi/static',

                'vendor'    => '/path/to/pi/vendor',

                'config'    => '/path/to/pi/config',
                'cache'     => '/path/to/pi/cache',
                'log'       => '/path/to/pi/log',
            ),
        ),
    ),
);
