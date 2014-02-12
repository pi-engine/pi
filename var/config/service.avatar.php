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
    //'adapter'       => 'upload',
    // Auto detected
    'adapter'       => array('upload', 'select', 'gravatar'),

    // Options for named size
    'size_map'  => array(
        'mini'      => 16,
        'xmall'     => 24,
        'small'     => 28,
        'medium'    => 46,
        'normal'    => 80,
        'large'     => 96,
        'xlarge'    => 120,
        'xxlarge'   => 214,
        'origin'    => 300,
    ),

    'local' => array(
        // Options for available size
        'size_list'  => array(
            'mini',
            'small',
            'medium',
            'normal',
            'xlarge',
        ),
    ),

    // Options for gravatar
    'gravatar'  => array(
        //'default'   => 'http://pialog.org/static/avatar/normal.png',
        'extension' => 'png',
        'rate'      => 'g',
        //'secure'    => true,

        // Options for available size
        'size_list'  => array(
            'mini',
            'small',
            'medium',
            'normal',
            'xlarge',
            //'origin',
        ),
    ),

    // Options for selective avatars
    'select'    => array(
        // Path to avatar root
        //'root_path' => Pi::path('static/avatar/select'),
        // URL to avatar root
        //'root_url'  => Pi::url('static/avatar/select', true),
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

        // Options for available size
        'size_list'     => array(
            'mini',
            'normal',
            'xlarge',
            'xxlarge',
            //'origin',
        ),
    ),

    // Options for upload avatars
    'upload'    => array(
        // Allowed image extensions
        'extension' => array('jpg', 'gif', 'png', 'bmp'),
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
            $result = md5($data['uid']) .  '.' . $data['extension'];
            return $result;
        },

        // Options for available size
        'size_list'  => array(
            'mini',
            'small',
            'medium',
            'normal',
            'xlarge',
            'origin',
        ),
    ),

    // Options for QQ avatar
    // Not implemented yet, placeholder
    'qq'    => array(
        'api'       => '',
        'size_list'  => array(
            'small',
            'normal',
            'large',
        ),
    ),

);
