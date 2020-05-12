<?php
// Session service configuration

return [
    // Configs
    'config'            => [
        'class'      => 'Laminas\Session\Config\SessionConfig',
        // Runtime session configurations
        // @see http://www.php.net/manual/en/session.configuration.php
        'options'    => [
            // cookie name for session
            'name'                => 'pisess',

            // lifetime of the cookie in seconds which is sent to the browser
            'cookie_lifetime'     => 0,
            // domain of the cookie
            //'cookie_domain'         => '',
            // path where information is stored
            //'cookie_path'           => '',

            // time-to-live for cached session pages in minutes
            'cache_expire'        => 180,

            // upload progress
            // Not used yet
            //'upload_progress'       => array(),

            // Remember Me in seconds, two weeks
            'remember_me_seconds' => 1209600,
        ],
        // Validators: validator class => data
        'validators' => [
            'Laminas\Session\Validator\HttpUserAgent'
            // The following RemoteAddr validator must be disabled when "RememberMe" is enabled
            //'Laminas\Session\Validator\RemoteAddr',
        ],
    ],

    // Storage
    'storage'           => [
        //'class' => 'Laminas\Session\Storage\SessionStorage',
        'class' => 'Laminas\Session\Storage\SessionArrayStorage',
        'input' => [
        ],
    ],
    // SaveHandler, DbTable
    'save_handler'      => [
        'class'   => 'Pi\Session\SaveHandler\DbTable',
        'options' => [
        ],
    ],
    // Probability to clear expired containers, valid value: 1 - 100
    'clear_probability' => 10,
];
