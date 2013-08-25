<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

return array(
    'meta'  => array(
        'title'         => 'User',
        'description'   => 'User profile and services.',
        'version'       => '1.0.0-alpha',
        'license'       => 'New BSD',
        'demo'          => 'http://demo.pialog.org'
    ),
    // Author information
    'author'    => array(
        // Author full name, required
        'name'      => 'Taiwen Jiang',
        // Email address, optional
        'email'     => 'taiwenjiang@tsinghua.org.cn',
    ),

    // Resource
    'resource' => array(
        // Database meta
        'database'  => array(
            // SQL schema/data file
            'sqlfile'   => 'sql/mysql.sql',
        ),
        'config'    => 'config.php',
        'user'      => 'user.php',
        'route'     => 'route.php',
        'nav'       => 'nav.php',
    ),
);
