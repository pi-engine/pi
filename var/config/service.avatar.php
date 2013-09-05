<?php
// User avatar service configuration

return array(
    // Gravatar.com
    'adapter'       => 'gravatar',
    // System local avatars
    'adapter'       => 'local',
    // System select avatars
    'adapter'       => 'select',
    // User uploaded avatars
    'adapter'       => 'upload',
    // Auto detected
    'adapter'       => 'auto',

    // Allowed adapters
    'adapter_allowed' => array('upload', 'select', 'gravatar'),

    // Options for named size
    'size_map'  => array(
        'mini'      => 16,
        'xmall'     => 'small', //24,
        's'         => 'small',
        'small'     => 28,
        'm'         => 'medium',
        'medium'    => 'normal', //46,
        'normal'    => 80,
        'l'         => 'large',
        'large'     => 'xlarge', //96,
        'x'         => 'xlarge',
        'xlarge'    => 120,
        'xx'        => 'xxlarge',
        'xxlarge'   => 'origin', //214,
        'max'       => 'origin',
        'o'         => 'origin',
        'origin'    => 300,
    ),

    // Options for gravatar
    'gravatar'  => array(
        //'default'   => 'http://pialog.org/static/avatar/normal.png',
        'extension' => 'png',
        'rate'      => 'g',
        //'secure'    => true,
    ),

    // Options for selective avatars
    'select'    => array(
        // Path to avatar root
        'root_path' => Pi::path('static/avatar'),
        // URL to avatar root
        'root_url'  => Pi::url('static/avatar', true),
        'extension' => 'png',
        // Callback for path with parameters: source file name, size
        'path'      => function($data) {
            $path = sprintf(
                '%s/%s' . '.png',
                $data['source'],
                $data['size']
            );

            return $path;
        },
    ),

    // Options for upload avatars
    'upload'    => array(
        // Path to avatar root
        'root_path' => Pi::path('upload/avatar'),
        // URL to avatar root
        'root_url'  => Pi::url('upload/avatar', true),
        // Callback for path with parameters: uid, source file name, size
        // File number limit in a folder as 10000 (defined by `$fileLimit`)
        'path'  => function($data) {
            $fileLimit = 10000;
            $uid = $data['uid'];
            $sn = str_pad(round($uid / $fileLimit) + 1, 4, '0', STR_PAD_LEFT);
            $path = sprintf(
                '%s/%s/%s',
                $sn,
                $data['size'],
                $data['source']
            );

            return $path;
        },
        // Callback to generate source file name using uid, source, extension
        'source_hash'   => function ($data) {
            // Return $data['source'] will skip hash
            $result = md5(uniqid($data['uid'])) .  '.' . $data['extension'];
            return $result;
        },
    ),

    // Options for QQ avatar
    // Not implemented yet, placeholder
    'qq'    => array(
        'api'       => '',
        'size_map'  => array(
            'small'     => 30,
            'normal'    => 50,
            'large'     => 100,
        ),
    ),

);
