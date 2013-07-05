<?php
// Audit service configuration

$path = Pi::path('log');
return array(
    'full'  => array(
        'file'          => $path . '/full.log',
        'timeformat'    => 'c',
        'format'        => '%time% %d %s [%s]',
    ),
    'csv'   => array(
        'file'          => $path . '/csv.log',
        'format'        => 'csv',
        'timeformat'    => 'c',
    ),
    'relative'  => array(
        'timeformat'    => 'c',
    ),
    'lean'      => array(
        'file'          => $path . '/lean.log',
    ),
    'easy'      => $path . '/easy.log',
    'simple'    => array(),
);
