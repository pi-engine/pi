<?php
// Cache configuration for APC

$config = array(
    // Storage adapter
    'adapter'   => array(
        'name'  => 'apc',
        // Options
        'options'    => array(
            //'namespace' => Pi::config('identifier'),
        ),
    ),
    // Plugin list
    'plugins'   => array(
        'exception_handler' => array('throw_exceptions' => false),
    ),
);

return $config;
