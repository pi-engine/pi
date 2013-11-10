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
        'title'         => _a('Widget'),
        // Description, for admin, optional
        'description'   => _a('Management of custom blocks/widgets.'),
        // Version number, required
        'version'       => '1.0.0',
        // Distribution license, required
        'license'       => 'New BSD',
        // Logo image, for admin, optional
        'logo'          => 'image/logo.png',
        // Demo site link, optional
        'demo'          => 'http://pi-demo.org',
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
        'credits'   => 'Pi Engine Team; EEFOCUS Team.'
    ),

    // Resource
    'resource' => array(
        // Database meta
        'database'  => array(
            // SQL schema/data file
            'sqlfile'   => 'sql/mysql.sql',
            // Tables to be removed during uninstall
            'schema'    => array(
                'widget'          => 'table',
            )
        ),
        // Permission specs
        'permission'    => 'permission.php',
        // View pages
        'page'          => 'page.php',
        // Navigation definition
        'navigation'    => 'nav.php',
    ),
);
