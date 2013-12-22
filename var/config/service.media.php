<?php
// Media service configuration

return array(
    // Media access adapter
    'adapter'   => 'local',

    // Local media center
    'local'    => array(
        'class' => 'Pi\Media\Adapter\Local',
        'options'   => array(
            'root_path' => Pi::path('upload/media'),
            'root_uri'  => Pi::url('upload/media'),
        ),
    ),

    // Remote media center
    'remote'    => array(
        'class' => 'Pi\Media\Adapter\Remote',
        'options'   => array(
            // FTP for file upload transfer
            'ftp' => array(
                'username'  => '',
                'password'  => '',
                'timeout'   => 0,
            ),

            // Authorization for remote access
            'authorization' => array(

            ),
            // API URIs
            'uri'   => array(

            ),
        ),
    ),
);
