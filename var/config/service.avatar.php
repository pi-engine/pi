<?php
// User avatar service configuration

return array(
    // Gravatar.com
    'adapter'       => 'gravatar',
    // System local avatars
    'adapter'       => 'local',
    // User uploaded avatars
    //'adapter'       => 'upload',
    // Auto detected
    'adapter'       => 'auto',

    // Options for named size
    'size_map'  => array(
        'mini'      => 16,
        'xmall'     => 20,
        's'         => 'small',
        'small'     => 40,
        'm'         => 'medium',
        'medium'    => 60,
        'normal'    => 80,
        'l'         => 'large',
        'large'     => 100,
        'x'         => 'xlarge',
        'xlarge'    => 120,
        'xxlarge'   => 150,
        'max'       => 'origin',
        'o'         => 'origin',
        'origin'    => 200,
    ),

    // Options for gravatar
    'gravatar'  => array(
        //'default'   => 'http://pialog.org/static/avatar/normal.png',
        'extension' => 'png',
        'rate'      => 'g',
        //'secure'    => true,
    ),

    'upload'    => array(
        'path'  => function($data) {
            $path = sprintf(
                'upload/avatar/%s/%d-%s',
                $data['size'],
                $data['uid'],
                $data['source']
            );

            return $path;
        },
    ),
);
