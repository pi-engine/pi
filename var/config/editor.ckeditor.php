<?php
// CKEditor config

return array(
    'attributes'    => array(
    ),
    'options'   => array(
        'skin'      => 'kama',
        'width'     => '500px',
        'height'    => 200,
        'toolbar'   => array(
            array(
                'Bold',
                'Italic',
                'Strike',
                'FontSize',
            ),
            array(
                'NumberedList',
                'BulletedList',
                'Outdent',
                'Indent',
                'Blockquote',
            ),
            array(
                'Link',
                'Unlink',
                'Image',
                'Flash',
                'Source',
            ),
        ),

        'upload'    => array(
            'enabled'   => true,
            'path'      => '',
            'url'       => '',
        ),
    )
);
