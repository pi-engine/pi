<?php
// Cache configuration for APC

$config = array(
    // Storage adapter
    'adapter'   => array(
        'name'  => 'apc',
        // Options, see Zend\Cache\Storage\Adapter\ApcOptions
        'options'    => array(
        ),
    ),
    // Plugin list
    'plugins'   => array(
        'exception_handler' => array('throw_exceptions' => false),
    ),
);

return $config;
