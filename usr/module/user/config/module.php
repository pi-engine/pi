<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

/**
 * User module meta
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return [
    'meta'     => [
        'title'       => _a('User'),
        'description' => _a('User profile and services.'),
        'version'     => '1.7.1',
        'license'     => 'New BSD',
        'demo'        => 'http://demo.piengine.org',
        'icon'        => 'fa-user',
    ],
    // Author information
    'author'   => [
        // Author full name, required
        'Dev'       => 'Taiwen Jiang; Liu Chuang; Liaowei; Zongshu Lin',
        'Architect' => '@taiwen',
        'UI/UE'     => '@zhangsimon, @loidco, @voltan, Zeng Long',
        'QA'        => '@lavenderli, @MarcoXoops, Zhang Hua',
        // Email address, optional
        'Email'     => 'taiwenjiang@tsinghua.org.cn',
        // Website link, optional
        'Website'   => 'http://piengine.org',
        // Credits and acknowledgement, optional
        'Credits'   => 'Pi Engine Team; Zend Framework Team; EEFOCUS Team.',
    ],

    // Resource
    'resource' => [
        // Database meta
        'database'   => [
            // SQL schema/data file
            'sqlfile' => 'sql/mysql.sql',
        ],
        // Permission specs
        'permission' => 'permission.php',
        'config'     => 'config.php',
        'page'       => 'page.php',
        'route'      => 'route.php',
        'navigation' => 'nav.php',
        'event'      => 'event.php',
        'block'      => 'block.php',
        'user'       => 'user.php',
    ],
];
