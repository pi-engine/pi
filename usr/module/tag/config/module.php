<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */


/**
 * Application manifest
 */
return array(
    // Module meta
    'meta'  => array(
        // Module title, required
        'title'         => _a('Tags'),
        // Description, for admin, optional
        'description'   => _a('Tag'),
        // Version number, required
        'version'       => '1.1.0',
        // Distribution license, required
        'license'       => 'New BSD',
        // Logo image, for admin, optional
        'logo'          => 'image/logo.png',
        // Logo icon
        'icon'          => 'fa-tags',
    ),
    // Author information
    'author'    => array(
        'Dev'       => 'Taiwen Jiang; Liu Chuang; Liao Wei',
        'UI/UE'     => '@zhangsimon, @loidco',
        'QA'        => '@lavenderli',
        'Email'     => 'taiwenjiang@tsinghua.org.cn',
    ),
    // resource
    'resource' => array(
        // Database meta
        'database'  => array(
            'sqlfile'   => 'sql/mysql.sql',
            'schema'    => array(
                'tag'          => 'table',
                'link'         => 'table',
                'stats'        => 'table',
            ),
        ),
        // Navigation definition
        'navigation'    => 'navigation.php',
        // Config definition.
        'config'        => 'config.php',
        // Block definition.
        'block'         => 'block.php',
        'route'         => 'route.php',
    ),
);
