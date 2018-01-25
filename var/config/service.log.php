<?php
// Log service configuration

/**
 * Environments: production, debug, development
 */
$config = [];

// Development environment
$config['development'] = [
    // Global enable
    //'active'    => true,
    // IPs to access all debug information, only applicable if 'active' is not specified
    'ip'                => [],
    // Default logger
    'logger'            => [
        // Logger enable
        'active' => true,
        // Specified writers
        'writer' => [
            /*
            // Write logs to syslog
            'syslog'    => array(
                'application'   => 'Pi\Log',
                'facility'      => LOG_USER,
            ),
            */
            // Write logs to audit table
            'audit' => [
                // Roles of users to be logged
                'role'   => [],
                // User IDs to be logged
                'user'   => [],
                // User IPs to be logged
                'ip'     => [],
                // Request methods to be logged
                'method' => ['POST'],
            ],
        ],
    ],
    // Debug manager to gather and display debug messages
    'debugger'          => [
        // Debugger enable
        'active' => true,
    ],
    // Error handler
    'error_handler'     => [
        // Error handler enable
        'active'          => true,
        // error reporting
        'error_reporting' => -1,
        // Custom level for error_reporting, only applicable when error_reporting is not specified
        'error_level'     => 'development',
        // Log for fatal errors, false for disable
        'fatal_error_log' => false, //'fatal-error',
    ],
    // Exception handler
    'exception_handler' => [
        // Exception handler enable
        'active' => true,
    ],
    // Profiler
    'profiler'          => [
        // Profiler enable
        'active' => true,
    ],
    // DB profiler
    'db_profiler'       => [
        // DB profiler enable
        'active' => true,
    ],
];

// Debug environment
$config['debug']                                     = $config['development'];
$config['debug']['error_handler']['error_reporting'] = E_ALL & ~(E_DEPRECATED | E_USER_DEPRECATED | E_NOTICE);
$config['debug']['error_handler']['error_level']     = 'debug';

// Close environment
$config['close']                                     = $config['development'];
$config['close']['error_handler']['error_reporting'] = E_ALL;
$config['close']['ip']                               = ['127.0.0.1'];

// Production evnvironment
$config['production'] = [
    // Default logger
    'logger'            => [
        // Specified writers
        'writer' => [
            // Write logs to audit table
            'audit' => [
                // Roles of users to be logged
                'role'   => [],
                // User IDs to be logged
                'user'   => [],
                // User IPs to be logged
                'ip'     => [],
                // Request methods to be logged
                'method' => ['POST'],
            ],
        ],
    ],
    // Error handler
    'error_handler'     => [
        // Error handler enable
        'active'          => true,
        // error reporting
        'error_reporting' => E_USER_ERROR,
    ],
    // Exception handler
    'exception_handler' => [
        // Exception handler enable
        'active' => true,
    ],
];

return $config[Pi::environment()] ?: $config['production'];
