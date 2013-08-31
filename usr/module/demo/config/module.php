<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

return array(
    // Module meta
    'meta'  => array(
        // Module title, required
        'title'         => 'DEMO Sandbox',
        // Description, for admin, optional
        'description'   => 'Examples and tests for developers.',
        // Version number, required
        'version'       => '1.0.0-beta.1',
        // Distribution license, required
        'license'       => 'New BSD',
        // Logo image, for admin, optional
        'logo'          => 'image/logo.png',
        // Readme file, for admin, optional
        'readme'        => 'docs/readme.txt',
        // Demo site link, optional
        'demo'          => 'http://demo.pialog.org/demo',

        // Module is ready for clone? Default as false
        'clonable'      => true,
    ),
    // Author information
    'author'    => array(
        // Author full name, required
        'name'      => 'Taiwen Jiang',
        // Email address, optional
        'email'     => 'taiwenjiang@tsinghua.org.cn',
        // Website link, optional
        'website'   => 'http://pialog.org',
        // Credits and aknowledgement, optional
        'credits'   => 'Pi Engine Team'
    ),
    // Module dependency: list of module directory names, optional
    'dependency'    => array(
    ),

    // Resource
    'resource' => array(
        // Database meta
        'database'  => array(
            // SQL schema/data file
            'sqlfile'   => 'sql/mysql.sql',
            // Tables to be removed during uninstall
            'schema'    => array(
                'test'          => 'table',
                'page'          => 'table',
                //'test_view'     => 'view',
                //'test_trigger'  => 'trigger',
            ),
        ),
        // Module configs
        'config'    => 'config.php',
        // ACL specs
        'acl'       => 'acl.php',
        // Block definition
        'block'     => 'block.php',
        // Bootstrap, priority
        'bootstrap' => 1,
        // Event specs
        'event'     => 'event.php',
        // Search registry, 'class:method'
        'search'    => array('callback' => array('search', 'index')),
        // View pages
        'page'      => 'page.php',
        // Navigation definition
        'navigation'    => 'nav.php',
        // Routes, first in last out; bigger priority earlier out
        'route'     => 'route.php',
        // Callback for stats and monitoring
        'monitor'   => array('callback' => array('monitor', 'index')),
        // Additional custom extension
        'test'      => array(
            'config'    => 'For test',
        ),

        'user'      => 'user.php',
    ),
);
