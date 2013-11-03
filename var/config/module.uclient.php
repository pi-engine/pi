<?php
// Uclient configurations

$root           = 'http://master.pi';
$userRoot       = $root . '/user';
$apiUser        = $root . '/api/user/user';
$apiTimeline    = $root . '/api/user/timeline';
$apiMessage     = $root . '/api/message/message';
$apiRelation    = $root . '/api/user/relation';

return array (
    'app_key'        => md5(Pi::url('www')),

    'authorization' => array(
        'httpauth'  => '',
        'username'  => '',
        'password'  => '',
    ),

    'url'   => array(
        'meta'  => $apiUser . '/meta',
        'get'   => $apiUser . '/get',
        'mget'  => $apiUser . '/mget',
        'list'  => $apiUser . '/list',
        'count' => $apiUser . '/count',

        'avatar'    => $root . '/script/avatar.php?id=%d&s=%s',

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

        // Timeline API uri
        'timeline'  => array(
            'add'   => $apiTimeline . '/insert',
        ),
        // Message API uri
        'message'  => array(
            'send'      => $apiMessage . '/send',
            'notify'    => $apiMessage . '/notify',
            'count'     => $apiMessage . '/count',
            'alert'     => $apiMessage . '/alert',
        ),
        // Relation API uri
        'relation'  => array(
            'follow'    => $apiRelation . '/follow',
            'list'      => $apiRelation . '/list',
            'count'     => $apiRelation . '/count',
        ),
    ),
);
