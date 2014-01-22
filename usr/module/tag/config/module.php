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
        'title'         => _a('Tags'),
        // Description, for admin, optional
        'description'   => _a('Tag'),
        // Version number, required
        'version'       => '1.1.0',
        // Distribution license, required
        'license'       => 'New BSD',
        // Logo image, for admin, optional
        'logo'          => 'image/logo.png',
        'icon'          => 'fa fa-tags',
    ),
    // Author information
    'author'    => array(
        // Author full name, required
        'Dev'      => 'Chuang Liu; Taiwen Jiang; Liao Wei',
        // Email address, optional
        'Email'     => 'liuchuang@eefocus.com',
    ),
    // resource
    'resource' => array(
        // Database meta
        'database'  => array(
            // SQL schema/data file
            'sqlfile'   => 'sql/mysql.sql',
            // Tables to be removed during uninstall, optional - the table list will be generated automatically upon installation
            'schema'    => array(
                'tag'          => 'table',
                'link'         => 'table',
                'stats'        => 'table',
            ),
        ),
        // Navigation definition
        'navigation' => 'navigation.php',
        // Config definition.
        'config'     => 'config.php',
        // Block definition.
        'block'      => 'block.php',
    ),
);
