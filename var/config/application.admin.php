<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * Pi Engine admin application specifications
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */

// Inherit from `application.front.front`
$config = include __DIR__ . '/application.front.php';

// Translations
$config['resource']['i18n'] = [
    'translator' => [
        'global' => ['default', 'module/system:admin'],
        'module' => ['default', 'admin'],
    ],
];

// Permission check
$config['resource']['permission'] = [
    // Default access perm in case not defined: true for allowed, false for denied
    //'default_allow' => false,
    // Whether to check `site_close` for maintenance, default as true
    'check_close' => false,
    // If check page access
    'check_page'  => true,
    // Managed components
    'component'   => ['block', 'config', 'page', 'resource', 'event'],
    // Admin entrances
    'entrance'    => ['index', 'dashboard'],
];


// Render caching, disabled
$config['resource']['render_cache'] = false;

// Audit
/*
 * Options for recording:
 * skipError - skip error action
 * users - specific users to be logged
 * ips - specific IPs to be logged
 * roles - specific roles to be logged
 * pages - specific pages to be logged
 * methods - specific request methods to be logged
 */
$config['resource']['audit'] = [
    'skipError' => true,
    'methods'   => ['POST'],
];

// Admin mode detection
$config['resource']['admin_mode'] = [];

// Session settings
$config['resource']['session'] = [
    'service' => 'service.session-admin.php',
];

// Load authentication configs
$config['resource']['authentication'] = [
    'service' => [
        'strategy' => 'Local',
    ],
];

// Application service configuration
$config['application']['view_manager']['layout'] = 'layout-admin';

return $config;
