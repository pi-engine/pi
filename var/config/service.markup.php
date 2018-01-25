<?php
// Markup service configuration

return [
    'renderer' => [
        'text' => [
            'nl2br' => [],
        ],
        'html' => [
        ],
    ],
    'parser'   => [
        'text'     => [
            'nl2br'   => true,
            'filters' => [
                // Linkify
                // @see Pi\Filter\Linkify
                'linkify' => [],

                // User filter
                // @see Pi\Filter\User
                'user'    => [
                    'callback' => function ($value) {
                        $value = preg_replace_callback(
                            '`(^|\s)@([a-zA-Z0-9]{3,32})`',
                            function ($m) {
                                $url         = Pi::service('user')->getUrl(
                                    'profile',
                                    ['name' => $m[2]]
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
                ],

                // Tag filter
                // @see Pi\Filter\Tag
                'tag'     => [
                    'callback' => function ($value) {
                        $value = preg_replace(
                            '`(^|\s)\#(\w+)`',
                            '$1<a href="' . Pi::url('www') . '/tag/$2" title="$2">#$2</a>',
                            $value
                        );
                        return $value;
                    },
                ],

            ],
        ],
        'markdown' => [
            'filters' => [
            ],
        ],
        'html'     => [
            'safe_tags' => [],
            'filters'   => [
                // Xss filter
                // @see Pi\Filter\XssSanitizer
                'xss_sanitizer' => [
                    'length' => 8,
                ],
            ],
        ],
    ],
];
