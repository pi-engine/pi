<?php
// User service configuration

return array(
    'adapter'   => 'Pi\User\Adapter\Local',
    'adapter'   => 'Pi\User\Adapter\System',

    // Followings are optional
    'options'   => array(
        'authentication'    => 'service.authentication.php',
    ),

    'resource'  => array(
        'avatar'            => array(
            'class'         => '',
            'options'       => array(
                // Gravatar.com
                'adapter'       => 'gravatar',
                // System local avatars
                //'adapter'       => 'local',
                // User uploaded avatars
                //'adapter'       => 'upload',
                // Auto detected
                'adapter'       => 'auto',

                'options'       => array(

                    // Options for gravatar
                    'gravatar'  => array(
                        //'default'   => 'http://pialog.org/avatar/normal.jpg',
                        'extension' => 'png',
                        'rate'      => 'g',
                        //'secure'    => true,
                    ),

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
                ),
            ),
        ),
    ),
);
