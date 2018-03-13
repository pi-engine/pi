<?php
// MarkItUp config

return [
    'options' => [
        'skin' => 'pi',
        'set'  => 'html',
        'sets' => [
            'markdown' => [
                'parser_path' => Pi::url('www') . '/script/editor/markitup/preview/markdown.php',
            ],
            'bbcode'   => [
                //'parser_path'   => '',
            ],
        ],
    ],
    /*
    'attributes'    => array(
        'rows'  => 10,
        'cols'  => 80,
    ),
    */
];
