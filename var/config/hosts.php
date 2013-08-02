<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * Pi Engine multi-host specifications
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
                'custom'    => '/path/to/pi/custom',
                'cache'     => '/path/to/pi/cache',
                'log'       => '/path/to/pi/log',
            ),
        ),
    ),
);
