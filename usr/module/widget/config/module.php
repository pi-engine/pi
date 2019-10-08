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
        'title'       => _a('Widget'),
        // Description, for admin, optional
        'description' => _a('Management of custom blocks/widgets.'),
        // Version number, required
        'version'     => '2.1.3',
        // Distribution license, required
        'license'     => 'BSD 3-Clause',
        // Logo image, for admin, optional
        'logo'        => 'image/logo.png',
        // Demo site link, optional
        'demo'        => 'http://pi-demo.org',
        'icon'        => 'fa-cogs',
    ],
    // Author information
    'author'   => [
        // Author full name, required
        'Dev'     => 'Taiwen Jiang; Zongshu Lin; Liao Wei',
        // Email address, optional
        'Email'   => 'taiwenjiang@tsinghua.org.cn',
        // Website link, optional
        'Website' => 'http://piengine.org',
        // Credits and aknowledgement, optional
        'Credits' => 'Pi Engine Team; EEFOCUS Team.',
    ],

    // Resource
    'resource' => [
        // Database meta
        'database'   => [
            // SQL schema/data file
            'sqlfile' => 'sql/mysql.sql',
            // Tables to be removed during uninstall
            'schema'  => [
                'widget' => 'table',
            ],
        ],
        // Permission specs
        'permission' => 'permission.php',
        // View pages
        'page'       => 'page.php',
        // Navigation definition
        'navigation' => 'nav.php',
        'config'     => 'config.php',
    ],
];
