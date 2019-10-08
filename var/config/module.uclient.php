<?php
// Uclient configurations

// App key for remote user center
// Specify accordingly
$appKey = Pi::config('identifier');

// Root domain of remote user center
// Specify accordingly
$root = 'http://master.pi';

// Following derived specs
$userRoot    = $root . '/user';
$apiUser     = $root . '/api/user/user';
$apiAvatar   = $root . '/api/user/avatar';
$apiTimeline = $root . '/api/user/timeline';
$apiMessage  = $root . '/api/message/message';
$apiRelation = $root . '/api/user/relation';

return [
    'app_key' => $appKey,

    'authorization' => [
        'httpauth' => '',
        'username' => '',
        'password' => '',
    ],

    'url'      => [
        'meta'   => $apiUser . '/meta',
        'get'    => $apiUser . '/get',
        'mget'   => $apiUser . '/mget',
        'list'   => $apiUser . '/list',
        'count'  => $apiUser . '/count',

        //'avatar'    => $root . '/script/avatar.php?id=%d&s=%s',
        'avatar' => [
            'get'  => $apiAvatar . '/get',
            'mget' => $apiAvatar . '/mget',
            //'list'  => $apiAvatar . '/list',
        ],

        'profile'  => [
            'id'       => $userRoot . '/profile/%d',
            'name'     => $userRoot . '/profile/name/%s',
            'identity' => $userRoot . '/profile/identity/%s',
            'my'       => $userRoot . '/profile',
        ],
        'home'     => [
            'id'       => $userRoot . '/home/%d',
            'name'     => $userRoot . '/home/name/%s',
            'identity' => $userRoot . '/home/identity/%s',
            'my'       => $userRoot . '/home',
        ],
        //'login'     => $userRoot . '/login',
        //'logout'    => $userRoot . '/logout',
        'register' => $userRoot . '/register?app=' . $appKey,

    ],

    // Timeline API uri
    'timeline' => [
        'api' => [
            'add' => $apiTimeline . '/insert',
        ],
    ],
    // Message API uri
    'message'  => [
        'link' => $root . '/message',
        'api'  => [
            'send'   => $apiMessage . '/send',
            'notify' => $apiMessage . '/notify',
            'count'  => $apiMessage . '/count',
            'alert'  => $apiMessage . '/alert',
        ],
    ],
    // Relation API uri
    'relation' => [
        'api' => [
            'follow' => $apiRelation . '/follow',
            'list'   => $apiRelation . '/list',
            'count'  => $apiRelation . '/count',
        ],
    ],
];
