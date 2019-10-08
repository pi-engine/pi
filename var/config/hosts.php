<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * Pi Engine multi-host specifications
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */

return [
    // Alias of hosts
    // Root URL => identifier of specifications
    'alias' => [
        'http://pi-engine.org'     => 'default',
        'http://www.pi-engine.org' => 'default',
        'http://pi-demo.org'       => 'demo',
        'http://www.pi-demo.org'   => 'demo',
        'http://piengine.org'      => 'org',
        'http://www.piengine.org'  => 'org',
    ],
    // Host specifications
    'hosts' => [
        // Specifications defined by files
        'demo'    => __DIR__ . '/host.demo.php',
        'org'     => '/path/to/org/config/host.php',

        // Specifications defined instantly
        'default' => [
            // URIs to resources
            'uri'  => [
                'www'    => 'http://pi-engine.org',
                'upload' => 'http://pi-engine.org/upload',
                'static' => 'http://pi-engine.org/static',
            ],
            // Paths to resources
            'path' => [
                'usr'    => '/path/to/pi/usr',
                'var'    => '/path/to/pi/var',
                'module' => '/path/to/pi/usr/module',
                'theme'  => '/path/to/pi/usr/theme',
                'custom' => '/path/to/pi/usr/custom',

                'upload' => '/path/to/pi/upload',
                'static' => '/path/to/pi/static',

                'vendor' => '/path/to/pi/vendor',

                'config' => '/path/to/pi/config',
                'cache'  => '/path/to/pi/cache',
                'log'    => '/path/to/pi/log',
            ],
        ],
    ],
];
