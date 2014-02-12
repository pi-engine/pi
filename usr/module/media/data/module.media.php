<?php
return array(
    'upload'    => array(
        'path'  => function($data) {
            $relativePath = 'upload/media';
            $fileLimit = 10000;
            $id = $data['id'];
            $sn = str_pad(round($id / $fileLimit) + 1, 4, '0', STR_PAD_LEFT);
            $path = sprintf(
                '%s/%s/%s',
                $relativePath,
                $data['type'],
                $sn
            );

            return $path;
        },
        // Callback to generate source file name using file id, extension
        'source_hash'   => function ($data) {
            $result = md5($data['id']) .  '.' . $data['extension'];
            return $result;
        },
    ),
);
