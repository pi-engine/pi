<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * User module meta
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return array(
    'meta'  => array(
        'title'         => _a('User'),
        'description'   => _a('User profile and services.'),
        'version'       => '1.3.5',
        'license'       => 'New BSD',
        'demo'          => 'http://demo.pialog.org',
        'icon'          => 'fa-user',
    ),
    // Author information
    'author'    => array(
        // Author full name, required
        'Dev'      => 'Taiwen Jiang; Liu Chuang; Liaowei; Zongshu Lin',
        'Architect' => '@taiwen',
        'UI/UE'     => '@zhangsimon, @loidco, @voltan, Zeng Long',
        'QA'        => '@lavenderli, @MarcoXoops, Zhang Hua',
        // Email address, optional
        'Email'     => 'taiwenjiang@tsinghua.org.cn',
        // Website link, optional
        'Website'   => 'http://pialog.org',
        // Credits and acknowledgement, optional
        'Credits'   => 'Pi Engine Team; Zend Framework Team; EEFOCUS Team.'
    ),

    // Resource
    'resource' => array(
        // Database meta
        'database'  => array(
            // SQL schema/data file
            'sqlfile'   => 'sql/mysql.sql',
        ),
        // Permission specs
        'permission'    => 'permission.php',
        'config'        => 'config.php',
        'user'          => 'user.php',
        'page'          => 'page.php',
        'route'         => 'route.php',
        'navigation'    => 'nav.php',
        'event'         => 'event.php',
    ),
);
