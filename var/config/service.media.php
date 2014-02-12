<?php
// Media service configuration

// Root of remote media center
$apiRoot = 'http://master.pi/api/media/doc';

$config = array(
    // Media access adapter
    'adapter'   => 'local',

    // Local media center
    'local'    => array(
        'class' => 'Pi\Media\Adapter\Local',
        'options'   => array(
            'root_path' => Pi::path('upload/media'),
            'root_uri'  => Pi::url('upload/media', true),
            'locator'   => array(
                // Path generator
                'path'  => function ($time = null) {
                        return date('Y/m/d', $time ?: time());
                    },
                // Filename generator
                'file'  => function ($source) {
                        $extension = pathinfo($source, PATHINFO_EXTENSION);
                        return uniqid() . '.' . $extension;
                    },
            ),
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
            'api'   => array(
                'add'           => $apiRoot . '/insert',
                'update'        => $apiRoot . '/update',
                'upload'        => $apiRoot . '/upload',
                'download'      => $apiRoot . '/download/id/%s',
                'get'           => $apiRoot . '/get',
                'mget'          => $apiRoot . '/mget',
                'list'          => $apiRoot . '/list',
                'count'         => $apiRoot . '/count',
                'delete'        => $apiRoot . '/delete',
                'stats'         => $apiRoot . '/stats',
                'stats_list'    => $apiRoot . '/mstats',
            ),
        ),
    ),
);

return $config;