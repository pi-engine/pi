<?php
// CKEditor config
$baseUrl = Pi::url('');
$url = $baseUrl . '/script/editor/ckeditor/plugins/articlepagebreak/assets/articlepagebreak.css';
return array(
    'attributes'    => array(
    ),
    'options'   => array(
        'skin'         => 'kama',
        'width'        => 'auto',
        'height'       => 500,
        //'language'     => 'zh-cn',
        'uiColor'      => '#EFF4F8',
        'extraPlugins' => "articlepagebreak",
        'contentsCss'  => $url,
        'toolbar'      => array(
            array(
                'Paste',
                'PasteText',
                'PasteFromWord',
            ),
            array(
                'Bold',
                'Italic',
                'Underline'
            ),
            array(
                'Format',
                'Font',
                 'FontSize',
            ),
            array(
                'Strike',
                'TextColor',
                'BGColor',
                'Subscript',
                'Superscript',
                'Replace'
            ),
            array(
               'Source',
               'Maximize',
                '/',
            ),
            '/',
            array(
                'JustifyLeft',
                'JustifyCenter',
                'JustifyRight',
                'JustifyBlock',
            ),
            array(
                'NumberedList',
                'BulletedList',
                '-',
                'Outdent',
                'Indent',
                '-',
                'Blockquote',
            ),
            array(
                'articlepagebreak',
                'Image',
                'Link',
                'Unlink',
                'Table',
                'Flash',
            ),
            array(
                'SpellChecker',
                'Undo',
                'Redo'
            )
        ),

        'upload'    => array(
            'enabled'   => true,
            'path'      => '',
            'url'       => '',
        ),
    )
);
