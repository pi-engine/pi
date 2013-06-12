<?php
// Internationalization specifications

return array(
    // To be loaded from system config if not specified
    // To be detected from user browser automatically if specified as 'autodetect'
    'locale'    => 'auto',
    // To be loaded from system config if not specified
    'charset'   => null,
    // Translations to be loaded on bootstrap
    'translator'    => array(
        // Global available
        'global'    => array('usr:admin'),
        // Module wide
        'module'    => array('admin'),
    )
);
