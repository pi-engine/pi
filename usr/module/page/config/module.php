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
        'title'         => 'Page',
        // Description, for admin, optional
        'description'   => 'Page configurations for cache, blocks and ACL.',
        // Version number, required
        'version'       => '1.0.0-beta.4',
        // Distribution license, required
        'license'       => 'New BSD',
        // Logo image, for admin, optional
        'logo'          => 'image/logo.png',
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
        'credits'   => 'Pi Engine Team; @voltan'
    ),

    // Resource
    'resource' => array(
        // Database meta
        'database'  => array(
            // SQL schema/data file
            'sqlfile'   => 'sql/mysql.sql',
            // Tables to be removed during uninstall
            'schema'    => array(
                'page'          => 'table',
                'stats'         => 'table',
            )
        ),
        // Navigation definition
        'navigation'    => 'nav.php',
        // Routes, first in last out; bigger priority earlier out
        'route'         => 'route.php',
        // View pages
        'page'          => 'page.php',
    ),
);
