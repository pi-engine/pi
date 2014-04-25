<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * Pi Engine admin application specifications
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */

// Inherit from `application.front.front`
$config = include __DIR__ . '/application.front.php';

// Translations
$config['resource']['i18n'] = array(
    'translator'    => array(
        'global'    => array('default', 'module/system:admin'),
        'module'    => array('default', 'admin'),
    ),
);

// Permission check
$config['resource']['permission'] = array(
    // Default access perm in case not defined: true for allowed, false for denied
    //'default_allow' => false,
    // Whether to check `site_close` for maintenance, default as true
    'check_close'   => false,
    // If check page access
    'check_page'    => true,
    // Managed components
    'component'     => array('block', 'config', 'page', 'resource', 'event'),
    // Admin entrances
    'entrance'      => array('index', 'dashboard'),
);


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
$config['resource']['audit'] = array(
    'skipError' => true,
    'methods'   => array('POST'),
);

// Admin mode detection
$config['resource']['admin_mode'] = array();

// Session settings
$config['resource']['session'] = array(
    'service'   => 'service.session-admin.php',
);

// Load authentication configs
$config['resource']['authentication'] = array(
    'service'   => array(
        'strategy'  => 'Local',
    ),
);

// Application service configuration
$config['application']['view_manager']['layout'] = 'layout-admin';

return $config;
