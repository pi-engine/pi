<?php
// CKEditor config
$baseUrl = Pi::url('');
$url     = $baseUrl . '/script/editor/ckeditor/plugins/articlepagebreak/assets/articlepagebreak.css';
return [
    'attributes' => [
    ],
    'options'    => [
        'skin'         => 'kama',
        'width'        => 'auto',
        'height'       => 500,
        //'language'     => 'zh-cn',
        'uiColor'      => '#EFF4F8',
        'extraPlugins' => "articlepagebreak",
        'contentsCss'  => $url,
        'toolbar'      => [
            [
                'Paste',
                'PasteText',
                'PasteFromWord',
            ],
            [
                'Bold',
                'Italic',
                'Underline',
            ],
            [
                'Format',
                'Font',
                'FontSize',
            ],
            [
                'Strike',
                'TextColor',
                'BGColor',
                'Subscript',
                'Superscript',
                'Replace',
            ],
            [
                'Source',
                'Maximize',
                '/',
            ],
            '/',
            [
                'JustifyLeft',
                'JustifyCenter',
                'JustifyRight',
                'JustifyBlock',
            ],
            [
                'NumberedList',
                'BulletedList',
                '-',
                'Outdent',
                'Indent',
                '-',
                'Blockquote',
            ],
            [
                'articlepagebreak',
                'Image',
                'Link',
                'Unlink',
                'Table',
                'Flash',
            ],
            [
                'SpellChecker',
                'Undo',
                'Redo',
            ],
        ],

        'upload' => [
            'enabled' => true,
            'path'    => '',
            'url'     => '',
        ],
    ],
];
