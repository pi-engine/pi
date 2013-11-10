<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * User module meta
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return array(
    'meta'  => array(
        'title'         => _a('Comment'),
        'description'   => _a('Comment management and services.'),
        'version'       => '1.0.0-alpha',
        'license'       => 'New BSD',
        'demo'          => 'http://demo.pialog.org'
    ),
    // Author information
    'author'    => array(
        // Author full name, required
        'dev'       => 'Taiwen Jiang',
        // Email address, optional
        'email'     => 'taiwenjiang@tsinghua.org.cn',
    ),

    // Resource
    'resource' => array(
        // Database meta
        'database'      => array(
            // SQL schema/data file
            'sqlfile'   => 'sql/mysql.sql',
        ),
        'config'        => 'config.php',
        'user'          => 'user.php',
        'block'         => 'block.php',
        'navigation'    => 'nav.php',
        'route'         => 'route.php',
        'comment'       => 'comment.php',
        'event'         => 'event.php',
    ),
);
