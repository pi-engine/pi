<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

return [
    // Module meta
    'meta'     => [
        // Module title, required
        'title'       => _a('Page'),
        // Description, for admin, optional
        'description' => _a('Single page for direct content display.'),
        // Version number, required
        'version'     => '1.3.2',
        // Distribution license, required
        'license'     => 'New BSD',
        // Logo image, for admin, optional
        'logo'        => 'image/logo.png',
        // Demo site link, optional
        'demo'        => 'http://demo.piengine.org/demo',

        'icon' => ' far fa-file-alt',

        'clonable' => true,
    ],
    // Author information
    'author'   => [
        // Author full name, required
        'Dev'     => 'Taiwen Jiang; Voltan; Liao Wei',
        // Email address, optional
        'Email'   => 'taiwenjiang@tsinghua.org.cn',
        // Website link, optional
        'Website' => 'http://piengine.org',
        // Credits and acknowledgement, optional
        'Credits' => 'Pi Engine Team',
    ],

    // Resource
    'resource' => [
        // Database meta
        'database'   => [
            // SQL schema/data file
            'sqlfile' => 'sql/mysql.sql',
        ],
        // Module Config
        'config'     => 'config.php',
        // Navigation definition
        'navigation' => 'nav.php',
        // Routes, first in last out; bigger priority earlier out
        'route'      => 'route.php',
        // View pages
        'page'       => 'page.php',
        // Comment
        'comment'    => 'comment.php',
    ],
];
