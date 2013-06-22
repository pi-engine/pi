<?php
// Markup service configuration

return array(
    'encoding'  => '',
    'filters'   => array(
        // User filter
        // @see Pi/Filter/User
        'user'  => array(
            'tag'           => '%term%',
            'pattern'       => '@([a-zA-Z0-9]{3,32})',
            //'replacement'   => '<a href="' . Pi::url('www') . '/user/%term%" title="%term%">@%term%</a>',
        ),
        // Tag filter
        // @see Pi/Filter/Tag
        'tag'   => array(
            'tag'           => '%term%',
            'pattern'       => '#([^\s\,]{3,32})#',
            'replacement'   => '<a href="' . Pi::url('www') . '/tag/%term%" title="%term%">#%term%#</a>',
        ),
    ),
);
