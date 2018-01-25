<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link         http://code.piengine.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://piengine.org
 * @license      http://piengine.org/license.txt BSD 3-Clause License
 */

/**
 * Module config and meta
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
return [
    // Module meta
    'meta'       => [
        'title'       => _a('Article'),
        'description' => _a('General module for content management.'),
        'version'     => '1.1.1',
        'license'     => 'New BSD',
        'logo'        => 'image/logo.png',
        'readme'      => 'README.md',
        'icon'        => 'fa-edit',
        'clonable'    => true,
    ],
    // Author information
    'author'     => [
        'Name'    => 'Zongshu Lin',
        'Email'   => 'zongshu@eefocus.com',
        'Website' => 'http://www.github.com/linzongshu',
        'QA'      => '@lavenderli',
        'Credits' => 'Pi Engine Team.',
    ],
    // Module dependency: list of module directory names, optional
    'dependency' => [
    ],
    // Maintenance resources
    'resource'   => [
        'database'   => [
            'sqlfile' => 'sql/mysql.sql',
        ],
        // Database meta
        'navigation' => 'navigation.php',
        'block'      => 'block.php',
        'config'     => 'config.php',
        'route'      => 'route.php',
        'permission' => 'permission.php',
        'page'       => 'page.php',
        'comment'    => 'comment.php',
    ],
];
