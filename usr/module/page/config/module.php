<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

return array(
    // Module meta
    'meta'  => array(
        // Module title, required
        'title'         => _a('Page'),
        // Description, for admin, optional
        'description'   => _a('Single page for direct content display.'),
        // Version number, required
        'version'       => '1.2.2',
        // Distribution license, required
        'license'       => 'New BSD',
        // Logo image, for admin, optional
        'logo'          => 'image/logo.png',
        // Demo site link, optional
        'demo'          => 'http://demo.pialog.org/demo',

        'icon'          => 'fa-file-text-o',

        'clonable'      => true,
    ),
    // Author information
    'author'    => array(
        // Author full name, required
        'Dev'       => 'Taiwen Jiang; Voltan; Liao Wei',
        // Email address, optional
        'Email'     => 'taiwenjiang@tsinghua.org.cn',
        // Website link, optional
        'Website'   => 'http://pialog.org',
        // Credits and acknowledgement, optional
        'Credits'   => 'Pi Engine Team'
    ),

    // Resource
    'resource' => array(
        // Database meta
        'database'  => array(
            // SQL schema/data file
            'sqlfile'   => 'sql/mysql.sql',
        ),
        // Module Config
        'config'        => 'config.php',
        // Navigation definition
        'navigation'    => 'nav.php',
        // Routes, first in last out; bigger priority earlier out
        'route'         => 'route.php',
        // View pages
        'page'          => 'page.php',
    ),
);
