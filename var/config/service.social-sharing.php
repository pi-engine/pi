<?php
// Social sharing specifications: title, icon, url

$config = [
    'items' => [
        'email'      => [
            'title' => __('Email'),
            'icon'  => 'fa fa-at',
            'url'   => 'mailto:?subject=%title%;body=%url%',
        ],
        'facebook'   => [
            'title' => __('Facebook'),
            'icon'  => 'fab fa-facebook',
            'url'   => 'https://www.facebook.com/sharer/sharer.php?u=%url%',
        ],
        'twitter'    => [
            'title' => __('Twitter'),
            'icon'  => 'fab fa-twitter',
            'url'   => 'http://www.twitter.com/home?status=%title%%url%',
        ],
        'tumblr'     => [
            'title' => __('Tumblr'),
            'icon'  => 'fab fa-tumblr',
            'url'   => 'http://tumblr.com/share?s=&amp;v=3&t=%title%&amp;u=%url%',
        ],
        'linkedin'   => [
            'title' => __('LinkedIn'),
            'icon'  => 'fab fa-linkedin',
            'url'   => 'http://www.linkedin.com/shareArticle?mini=true&amp;url=%url%&amp;title=%title%&amp;summary=%title%',
        ],
        'pinterest'  => [
            'title' => __('Pinterest'),
            'icon'  => 'fab fa-pinterest',
            'url'   => 'http://www.pinterest.com/pin/create/button/?url=%url%&amp;media=%image%&amp;description=%title%',
        ],
        'telegram'  => [
            'title' => __('Telegram'),
            'icon'  => 'fab fa-telegram',
            'url'   => 'https://telegram.me/share/?url=%url%&text=%title%',
        ],
        'whatsapp'  => [
            'title' => __('WhatsApp'),
            'icon'  => 'fab fa-whatsapp',
            'url'   => 'whatsapp://send?text=%title%-%url%',
        ],
    ],
];

return $config;
