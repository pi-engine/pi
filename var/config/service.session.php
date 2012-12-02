<?php
// Session service configuration

return array(
    // Configs
    'config'    => array(
        'class'     => 'Zend\\Session\\Config\\SessionConfig',
        // Runtime session configurations
        // @see http://www.php.net/manual/en/session.configuration.php
        'options'   => array(
            // cookie name for session
            'name'                  => 'xosession',

            // lifetime of the cookie in seconds which is sent to the browser
            'cookie_lifetime'       => 0,
            // domain of the cookie
            //'cookie_domain'         => '',
            // path where information is stored
            //'cookie_path'           => '',

            // time-to-live for cached session pages in minutes
            'cache_expire'          => 180,

            // upload progress
            // Not used yet
            //'upload_progress'       => array(),

            // Remember Me in seconds, two weeks
            'remember_me_seconds'   => 1209600,
        ),
    ),

    // Storage
    'storage'   => array(
        'class' => 'Zend\\Session\\Storage\\SessionStorage',
        'input' => array(
        ),
        /**/
        // Validators: validator class => data
        'validator' => array(
            'Zend\\Session\\Validator\\HttpUserAgent' => null,
            'Zend\\Session\\Validator\\RemoteAddr'    => null,
        )
        /**/
    ),
    // SaveHandler, DbTable
    'save_handler'  => array(
        'class'     => 'Pi\\Session\\SaveHandler\\DbTable',
        'options'   => array(
            'remember_me_'
        )
    ),
    /*
    // SaveHandler, cache
    'save_handler'  => array(
        'class'     => 'Pi\\Session\\SaveHandler\\Cache',
        'options'   => array(
            'class' => 'Zend\\Cache\\Storage\\Adapter\\Apc',
            'options'   => array(
            ),
    ),
    */
);
