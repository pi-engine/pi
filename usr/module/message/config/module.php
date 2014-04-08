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
        'title'         => _a('Message'),
        // Description, for admin, optional
        'description'   => _a('A module to send message'),
        // Version number, required
        'version'       => '1.0.0',
        // Distribution license, required
        'license'       => 'New BSD',
        // Module is ready for clone? Default as false
        'clonable'      => false,
        'icon'          => 'fa-envelope-o',
    ),
    // Author information
    'author'    => array(
        // Author full name, required
        'Dev'      => 'Xingyu Ji; Liu Chuang',
        // Email address, optional
        'Email'     => 'xingyu@eefocus.com',
        'UI/UE'     => '@zhangsimon, @loidco',
        'QA'        => 'Zhang Hua, @lavenderli',
        // Website link, optional
        'Website'   => 'http://pialog.org',
        // Credits and aknowledgement, optional
        'Credits'   => 'Zend Framework Team; Pi Engine Team; EEFOCUS Team.'
    ),
    // resource
    'resource' => array(
        // Database meta
        'database'  => array(
            // SQL schema/data file
            'sqlfile'   => 'sql/mysql.sql',
        ),
        // Navigation definition
        'navigation'    => 'navigation.php',
        // User specs
        'user'          => 'user.php',
    ),

);
