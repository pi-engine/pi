<?php
// Authentication strategy configuration

return array(
    // Source ID for simplesamlphp
    'source_id' => 'sp',

    // Storage
    'storage'   => array(
        'class' => 'Pi\Authentication\Storage\Session',
        'options' => array(
            'namespace' => 'PI_AUTH',
            'member'    => 'member',
        ),
    ),
);
