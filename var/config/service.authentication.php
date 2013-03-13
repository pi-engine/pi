<?php
// Authentication service configuration

return array(
    // Storage
    'storage'   => array(
        'class' => 'Pi\\Authentication\\Storage\\Session',
        'options' => array(
            'namespace' => 'PI_AUTH',
            'member'    => 'member',
        ),
    ),
    // Adapter
    'adapter'  => array(
        'class'     => 'Pi\\Authentication\\Adapter\\DbTable',
        'options'   => array(
        )
    ),
    //
);
