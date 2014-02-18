<?php
// MarkItUp config

return array(
    'options'   => array(
        'skin'      => 'pi',
        'set'       => 'html',
        'sets'      => array(
            'markdown'  => array(
                'parser_path'   => Pi::url('www') . '/script/editor/markitup/preview/markdown.php',
            ),
            'bbcode'    => array(
                //'parser_path'   => '',
            ),
        ),
    ),
    /*
    'attributes'    => array(
        'rows'  => 10,
        'cols'  => 80,
    ),
    */
);
