<?php
// Social sharing specifications: title, icon, url

$config = array(
    'items' => array(
        'email' => array(
            'title' => __('Email'),
            'icon'  => 'fa-at',
            'url'   => 'mailto:?subject=%title%;body=%url%',
        ),
        'facebook' => array(
            'title' => __('Facebook'),
            'icon'  => 'fa-facebook',
            'url'   => 'https://www.facebook.com/sharer/sharer.php?u=%url%',
        ),
        'twitter' => array(
            'title' => __('Twitter'),
            'icon'  => 'fa-twitter',
            'url'   => 'http://www.twitter.com/home?status=%title%%url%',
        ),
        'tumblr' => array(
            'title' => __('Tumblr'),
            'icon'  => 'fa-tumblr',
            'url'   => 'http://tumblr.com/share?s=&amp;v=3&t=%title%&amp;u=%url%',
        ),
        'linkedin' => array(
            'title' => __('LinkedIn'),
            'icon'  => 'fa-linkedin',
            'url'   => 'http://www.linkedin.com/shareArticle?mini=true&amp;url=%url%&amp;title=%title%&amp;summary=%title%',
        ),
        'googleplus' => array(
            'title' => __('Google+'),
            'icon'  => 'fa-google-plus',
            'url'   => 'https://plus.google.com/share?url=%title%%url%',
        ),
        'pinterest' => array(
            'title' => __('Pinterest'),
            'icon'  => 'fa-pinterest',
            'url'   => 'http://www.pinterest.com/pin/create/button/?url=%url%&amp;media=%image%&amp;description=%title%',
        ),
    ),
);

return $config;
