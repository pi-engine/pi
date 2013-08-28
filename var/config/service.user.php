<?php
// User service configuration

return array(
    //'adapter'   => 'Pi\User\Adapter\Local',
    'adapter'   => 'Pi\User\Adapter\System',

    // Followings are optional
    'options'   => array(
        'authentication'    => 'service.authentication.php',
    ),

    'resource'  => array(
        'avatar'            => array(
            'class'         => '',
            'options'       => array(
                'adapter'       => 'gravatar',
                //'adapter'       => 'local',
                //'adapter'       => 'upload',
                //'adapter'       => 'auto',

                'options'       => array(

                    // Options for gravatar
                    //'default'   => 'http://pialog.org/avatar/normal.jpg',
                    'extension' => 'png',
                    'rate'      => 'g',
                    'secure'    => true,

                    // Options for named size
                    'size_map'  => array(
                        'mini'      => 16,
                        'xmall'     => 'small',
                        's'         => 'small',
                        'small'     => 20,
                        'm'         => 'medium',
                        'medium'    => 60,
                        'normal'    => 80,
                        'l'         => 'large',
                        'large'     => 100,
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
