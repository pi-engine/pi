<?php
// CKEditor config

return [
    'attributes' => [
    ],
    'options'    => [
        'skin'    => 'kama',
        'width'   => '500px',
        'height'  => 200,
        'toolbar' => [
            [
                'Bold',
                'Italic',
                'Strike',
                'FontSize',
            ],
            [
                'NumberedList',
                'BulletedList',
                'Outdent',
                'Indent',
                'Blockquote',
            ],
            [
                'Link',
                'Unlink',
                'Image',
                'Flash',
                'Source',
            ],
        ],

        'upload' => [
            'enabled' => true,
            'path'    => '',
            'url'     => '',
        ],
    ],
];
