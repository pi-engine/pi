<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */


/**
 * Application manifest
 */
return [
    // Module meta
    'meta'     => [
        // Module title, required
        'title'       => _a('Tags'),
        // Description, for admin, optional
        'description' => _a('Tag'),
        // Version number, required
        'version'     => '1.1.0',
        // Distribution license, required
        'license'     => 'New BSD',
        // Logo image, for admin, optional
        'logo'        => 'image/logo.png',
        // Logo icon
        'icon'        => 'fa-tags',
    ],
    // Author information
    'author'   => [
        'Dev'   => 'Taiwen Jiang; Liu Chuang; Liao Wei',
        'UI/UE' => '@zhangsimon, @loidco',
        'QA'    => '@lavenderli',
        'Email' => 'taiwenjiang@tsinghua.org.cn',
    ],
    // resource
    'resource' => [
        // Database meta
        'database'   => [
            'sqlfile' => 'sql/mysql.sql',
            'schema'  => [
                'tag'   => 'table',
                'link'  => 'table',
                'stats' => 'table',
            ],
        ],
        // Navigation definition
        'navigation' => 'navigation.php',
        // Config definition.
        'config'     => 'config.php',
        // Block definition.
        'block'      => 'block.php',
        'route'      => 'route.php',
    ],
];
