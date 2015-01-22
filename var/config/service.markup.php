<?php
// Markup service configuration

return array(
    'renderer'  => array(
        'text'  => array(
            'nl2br' => array(),
        ),
        'html'  => array(
        ),
    ),
    'parser'    => array(
        'text'  => array(
            'nl2br'     => true,
            'filters'   => array(
                // Linkify
                // @see Pi\Filter\Linkify
                'linkify'   => array(),

                // User filter
                // @see Pi\Filter\User
                'user'  => array(
                    'callback'      => function ($value) {
                        $value = preg_replace_callback(
                            '`(^|\s)@([a-zA-Z0-9]{3,32})`',
                            function ($m) {
                                $url = Pi::service('user')->getUrl(
                                    'profile',
                                    array('name' => $m[2])
                                );
                                $escapedName = _escape($m[2]);
                                return sprintf(
                                    '%s<a href="%s" title="%s">@%s</a>',
                                    $m[1],
                                    $url,
                                    $escapedName,
                                    $escapedName
                                );
                            },
                            $value
                        );
                        return $value;
                    },
                ),

                // Tag filter
                // @see Pi\Filter\Tag
                'tag'   => array(
                    'callback'      => function ($value) {
                        $value = preg_replace(
                            '`(^|\s)\#(\w+)`',
                            '$1<a href="' . Pi::url('www') . '/tag/$2" title="$2">#$2</a>',
                            $value
                        );
                        return $value;
                    },
                ),

            ),
        ),
        'markdown'  => array(
            'filters'   => array(
            ),
        ),
        'html'  => array(
            'safe_tags' => array(),
            'filters'   => array(
                // Xss filter
                // @see Pi\Filter\XssSanitizer
                'xss_sanitizer'  => array(
                    'length'    => 8,
                ),
            ),
        ),
    ),
);
