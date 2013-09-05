<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * Application manifest
 */
return array(
    // Module meta
    'meta'  => array(
        // Module title, required
        'title'         => __('Message'),
        // Description, for admin, optional
        'description'   => __('A module to send message'),
        // Version number, required
        'version'       => '1.0.0',
        // Distribution license, required
        'license'       => 'New BSD',
        // Module is ready for clone? Default as false
        'clonable'      => false,
    ),
    // Author information
    'author'    => array(
        // Author full name, required
        'name'      => 'Xingyu Ji',
        // Email address, optional
        'email'     => 'xingyu@eefocus.com',
        // Website link, optional
        'website'   => 'http://www.xoopsengine.org',
        // Credits and aknowledgement, optional
        'credits'   => 'Zend Framework Team; Pi Engine Team; EEFOCUS Team.'
    ),
    // Maintenance actions
    'maintenance'   => array(
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
    ),
);
