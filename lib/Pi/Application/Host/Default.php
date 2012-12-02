<?php

return $config = array(
    // list of host path: identifier => host path
    'location'  => array(
        'default'   => '/path/to/default/path/to/host',
        'site'      => '/path/to/site/path/to/host',
    ),
    // URI alias list: associative array pair: URI => identifier
    'alias' => array(
        'http://domain.pi'       => 'default',
        'http://domain.test'        => 'default',
        'http://www.domain.pi'   => 'default',
        'http://www.domain.test'    => 'default',

        'http://domain.site/www'        => 'site',
        'http://www.domain.site/www'    => 'site',
    ),
);
