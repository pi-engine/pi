<?php
// Uclient configurations

$root       = 'http://master.pi';
$apiRoot    = $root . '/api/user/user';
$userRoot   = $root . '/user';

return array (
    'authorization' => array(
        'httpauth'  => '',
        'username'  => '',
        'password'  => '',
    ),

    'url'   => array(
        'meta'  => $apiRoot . '/meta',
        'get'   => $apiRoot . '/get',
        'mget'  => $apiRoot . '/mget',
        'list'  => $apiRoot . '/list',
        'count' => $apiRoot . '/count',

        'profile'   => array(
            'id'        => $userRoot . '/profile/%d',
            'name'      => $userRoot . '/profile/name/%s',
            'identity'  => $userRoot . '/profile/identity/%s',
            'my'        => $userRoot . '/profile',
        ),
        'home'      => array(
            'id'        => $userRoot . '/home/%d',
            'name'      => $userRoot . '/home/name/%s',
            'identity'  => $userRoot . '/home/identity/%s',
            'my'        => $userRoot . '/home',
        ),
        'login'     => $userRoot . '/login',
        'logout'    => $userRoot . '/logout',
        'register'  => $userRoot . '/register',
    ),
);
