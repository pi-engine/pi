<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

/**
 * Config specs
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */

$category = [
    /*
    array(
        'name'  => 'general',
        'title' => _t('General'),
    ),
    */
    [
        'name'  => 'login',
        'title' => _t('Login'),
    ],
    [
        'name'  => 'register',
        'title' => _t('Registration'),
    ],
    [
        'name'  => 'account',
        'title' => _t('Account'),
    ],
    [
        'name'  => 'avatar',
        'title' => _t('Avatar'),
    ],
    [
        'name'  => 'oauth',
        'title' => _t('OAuth'),
    ],
    [
        'title' => _a('Cron'),
        'name' => 'cron'
    ],
];

$config = [
    // General
    'list_limit' => [
        'title'       => _t('List limit'),
        'description' => _t('Number of items on list page.'),
        'value'       => 20,
        'filter'      => 'int',
    ],

    'birthdate_format' => [
        'title'       => _t('Birthdate format'),
        'description' => _t('Format for birthdate display.'),
        'value'       => 'Y-m-d',
    ],

    'email_expiration' => [
        'title'       => _t('Email expiration'),
        'description' => _t('Expiration time for email/password reset (in hours).'),
        'value'       => 24,
        'filter'      => 'int',
    ],

    'email_confirm' => [
        'title'       => _t('Email confirmation'),
        'description' => _t('Email confirmation with token is required for email change.'),
        'edit'        => 'checkbox',
        'value'       => 1,
        'filter'      => 'int',
    ],

    'require_profile_complete' => [
        'title'       => _t('Profile complete'),
        'description' => _t('Require user to complete profile data.'),
        'edit'        => 'checkbox',
        'value'       => 0,
        'filter'      => 'int',
    ],

    'enable_modal' => [
        'title'       => _t('Enable modal for login / register'),
        'description' => _t('Login / register with modal, with GET redirect (hidden input instead). Modal template are located into system module, because used by user-bar template'),
        'edit'        => 'checkbox',
        'value'       => 0,
        'filter'      => 'int',
    ],

    'head_keywords' => [
        'title'       => _t('Head keywords'),
        'description' => _t('Head keywords for SEO.'),
        'value'       => _t('account,social,tools,privacy,settings,profile,user,login,register,password,avatar'),
    ],

    'shortcuts_enable' => [
        'title'       => _t('Enable shortcuts on dashboard'),
//        'description' => _t('Login / register with modal, with GET redirect (hidden input instead). Modal template are located into system module, because used by user-bar template'),
        'edit'        => 'checkbox',
        'value'       => 0,
        'filter'      => 'int',
    ],

    'side_menu'     => [
        'title'       => _t('Active modules for show on side bar'),
        'description' => '',
        'edit'        => [
            'type'    => 'multi_checkbox',
            'options' => [
                'options' => [
                    'guide'           => _t('Guide'),
                    'media'           => _t('Media'),
                    'shop'            => _t('Shop'),
                    'order'           => _t('Order'),
                    'message'         => _t('Message'),
                    'support'         => _t('Support'),
                    'event'           => _t('Event'),
                    'vote'            => _t('Vote'),
                    'favourite'       => _t('Favourite'),
                    'guide_favourite' => _t('Guide favourite'),
                    'video'           => _t('Video'),
                    'audio'           => _t('Audio'),
                    'gallery'         => _t('Gallery'),
                    'ask'             => _t('Ask'),
                    'subscription'    => _t('Subscription'),
                    'ads'             => _t('Ads'),
                    'notification'    => _t('Notification'),
                    'comment'         => _t('Comment'),
                ],
            ],
        ],
        'filter'      => 'array',
        'value'       => [
            'guide',
            'shop',
            'order',
            'message',
            'support',
            'event',
            'vote',
            'favourite',
            'video',
            'audio',
            'gallery',
            'ask',
            'subscription',
            'ads',
            'notification',
        ],
    ],

    // Login
    'login_disable' => [
        'title'       => _t('Login disable'),
        'description' => _t('Disable user login'),
        'edit'        => 'checkbox',
        'value'       => 0,
        'filter'      => 'int',
        'category'    => 'login',
    ],

    'login_captcha' => [
        'title'       => _t('Login CAPTCHA'),
        'description' => _t('Enable CAPTCHA for user login'),
        'edit'        => [
            'type'    => 'select',
            'options' => [
                'options' => [
                    0 => _t('No captcha'),
                    1 => _t('Standard captcha'),
                    2 => _t('New re-captcha'),
                ],
            ],
        ],
        'value'       => 0,
        'filter'      => 'int',
        'category'    => 'login',
    ],

    'rememberme' => [
        'title'       => _t('Remember me'),
        'description' => _t('Days to remember login, 0 for disable.'),
        'value'       => 14,
        'filter'      => 'int',
        'category'    => 'login',
    ],

    'login_field' => [
        'title'       => _t('Login field'),
        'description' => _t('Identity field(s) for authentication.'),
        'edit'        => [
            'type'       => 'select',
            'attributes' => [
                'multiple' => true,
            ],
            'options'    => [
                'value_options' => [
                    'identity' => _t('Username'),
                    'email'    => _t('Email'),
                ],
            ],
        ],
        'filter'      => 'array',
        'value'       => ['identity'],
        'category'    => 'login',
    ],

    'login_attempts' => [
        'title'       => _t('Maximum attempts'),
        'description' => _t('Maximum attempts allowed to try for user login'),
        'value'       => 5,
        'filter'      => 'int',
        'category'    => 'login',
    ],

    'login_modal_title' => [
        'title'       => _t('Login modal title'),
        'description' => _t('Title text for login modal in modal header'),
        'edit'        => 'text',
        'value'       => _t('Login'),
        'filter'      => 'string',
        'category'    => 'login',
    ],

    'login_description' => [
        'title'       => _t('Login description'),
        'description' => _t('Description text for login page side bar, html allowed'),
        'edit'        => 'textarea',
        'value'       => '',
        'filter'      => 'string',
        'category'    => 'login',
    ],

    // Register
    'register_disable'  => [
        'title'       => _t('Register disable'),
        'description' => _t('Disable user registration'),
        'edit'        => 'checkbox',
        'value'       => 0,
        'filter'      => 'int',
        'category'    => 'register',
    ],

    'register_newsletter_optin' => [
        'title'       => _t('Add newsletter optin when registering'),
        'description' => _t('Whatever the value, newletter optin will be always available on User Account, if Subscription module is installed'),
        'edit'        => 'checkbox',
        'value'       => 0,
        'filter'      => 'int',
        'category'    => 'register',
    ],

    'register_captcha' => [
        'title'       => _t('Register CAPTCHA'),
        'description' => _t('Enable CAPTCHA for user registration'),
        'edit'        => [
            'type'    => 'select',
            'options' => [
                'options' => [
                    0 => _t('No captcha'),
                    1 => _t('Standard captcha'),
                    2 => _t('New re-captcha'),
                ],
            ],
        ],
        'value'       => 1,
        'filter'      => 'int',
        'category'    => 'register',
    ],

    'register_activation' => [
        'title'       => _t('Activation'),
        'description' => _t('Activation mode for user accounts'),
        'edit'        => [
            'type'    => 'select',
            'options' => [
                'options' => [
                    'auto'     => _t('Automatically activated'),
                    'email'    => _t('Activated by user email'),
                    'approval' => _t('Activated by admin approval'),
                ],
            ],
        ],
        'filter'      => 'string',
        'value'       => 'email',
        'category'    => 'register',
    ],

    'activation_expiration' => [
        'title'       => _t('Activation expiration'),
        'description' => _t('Expiration time for activation email (in hours).'),
        'value'       => 24,
        'filter'      => 'int',
        'category'    => 'register',
    ],

    'register_notification' => [
        'title'       => _t('Email notification'),
        'description' => _t('Send email notification on register success by admin approval.'),
        'edit'        => 'checkbox',
        'value'       => 1,
        'filter'      => 'int',
        'category'    => 'register',
    ],

    'register_notification_admin' => [
        'title'       => _t('Email notification to admin'),
        'description' => _t('Send email notification for new user registration to admin'),
        'edit'        => 'checkbox',
        'value'       => 1,
        'filter'      => 'int',
        'category'    => 'register',
    ],

    'require_register_complete' => [
        'title'       => _t('Register complete'),
        'description' => _t('Require user to complete register data in an extra form.'),
        'edit'        => 'checkbox',
        'value'       => 0,
        'filter'      => 'int',
        'category'    => 'register',
    ],

    'register_term' => [
        'title'       => _t('Register term and conditions'),
        'description' => _t('Show term and conditions check box to user'),
        'edit'        => 'checkbox',
        'value'       => 0,
        'filter'      => 'int',
        'category'    => 'register',
    ],

    'register_term_url' => [
        'title'       => _t('Term and conditions page url'),
        'description' => _t('Set term and conditions page url, if empty term and conditions check box not active'),
        'edit'        => 'text',
        'value'       => '',
        'filter'      => 'string',
        'category'    => 'register',
    ],

    'register_modal_title' => [
        'title'       => _t('Register modal title'),
        'description' => _t('Title text for register modal in modal header'),
        'edit'        => 'text',
        'value'       => _t('Register'),
        'filter'      => 'string',
        'category'    => 'register',
    ],

    'register_description' => [
        'title'       => _t('Register description'),
        'description' => _t('Description text for register page side bar, html allowed'),
        'edit'        => 'textarea',
        'value'       => '',
        'filter'      => 'string',
        'category'    => 'register',
    ],

    // User account
    'uname_format'         => [
        'title'       => _t('Username format'),
        'description' => _t('Format of username for registration.'),
        'edit'        => [
            'type'    => 'select',
            'options' => [
                'options' => [
                    'strict'       => _t('Strict: alphabet or number only'),
                    'strict-space' => _t('Strict: alphabet, number or space only'),
                    'medium'       => _t('Medium: ASCII characters'),
                    'medium-space' => _t('Medium: ASCII characters and spaces'),
                    'loose'        => _t('Loose: multi-byte characters'),
                    'loose-space'  => _t('Loose: multi-byte characters and spaces'),
                ],
            ],
        ],
        'filter'      => 'string',
        'value'       => 'medium',
        'category'    => 'account',
    ],

    'uname_min' => [
        'title'       => _t('Minimum username'),
        'description' => _t('Minimum length of username for user registration'),
        'value'       => 3,
        'filter'      => 'int',
        'category'    => 'account',
    ],

    'uname_max'   => [
        'title'       => _t('Maximum username'),
        'description' => _t('Maximum length of username for user registration'),
        'value'       => 32,
        'filter'      => 'int',
        'category'    => 'account',
    ],
    'name_format' => [
        'title'       => _t('Display name format'),
        'description' => _t('Format of display name for registration.'),
        'edit'        => [
            'type'    => 'select',
            'options' => [
                'options' => [
                    'strict'       => _t('Strict: alphabet or number only'),
                    'strict-space' => _t('Strict: alphabet, number or space only'),
                    'medium'       => _t('Medium: ASCII characters'),
                    'medium-space' => _t('Medium: ASCII characters and spaces'),
                    'loose'        => _t('Loose: multi-byte characters'),
                    'loose-space'  => _t('Loose: multi-byte characters and spaces'),
                ],
            ],
        ],
        'filter'      => 'string',
        'value'       => 'loose-space',
        'category'    => 'account',
    ],

    'name_min' => [
        'title'       => _t('Minimum display name'),
        'description' => _t('Minimum length of display name for user registration'),
        'value'       => 3,
        'filter'      => 'int',
        'category'    => 'account',
    ],

    'name_max' => [
        'title'       => _t('Maximum display name'),
        'description' => _t('Maximum length of display name for user registration'),
        'value'       => 32,
        'filter'      => 'int',
        'category'    => 'account',
    ],

    'password_min' => [
        'title'       => _t('Minimum password'),
        'description' => _t('Minimum length of password for user registration'),
        'value'       => 5,
        'filter'      => 'int',
        'category'    => 'account',
    ],

    'password_max' => [
        'title'       => _t('Maximum password'),
        'description' => _t('Maximum length of password for user registration'),
        'value'       => 32,
        'filter'      => 'int',
        'category'    => 'account',
    ],

    'strenghten_password' => [
        'title'       => _t('Strenghten password'),
        'description' => _t('Add some complexity to password filling : min 1 uppercase character, min 1 lowercase character, min 1 number'),
        'edit'        => 'checkbox',
        'value'       => 0,
        'filter'      => 'int',
        'category'    => 'account',
    ],

    'uname_blacklist' => [
        'title'       => _t('Username blacklist'),
        'description' => _t('Reserved and forbidden username list, separated with `|`, regexp syntax is allowed.'),
        'edit'        => 'textarea',
        'value'       => 'webmaster|^pi|^admin',
        'category'    => 'account',
    ],

    'name_blacklist' => [
        'title'       => _t('Display blacklist'),
        'description' => _t('Reserved and forbidden display name list, separated with `|`, regexp syntax is allowed.'),
        'edit'        => 'textarea',
        'value'       => 'webmaster|^pi|^admin',
        'category'    => 'account',
    ],

    'email_blacklist'   => [
        'title'       => _t('Email blacklist'),
        'description' => _t('Forbidden email list, separated with `|`, regexp syntax is allowed.'),
        'edit'        => 'textarea',
        'value'       => 'pi-engine.org$',
        'category'    => 'account',
    ],

    // Avatar
    // Allowed width of avatar image, 0 for no limit
    'max_avatar_width'  => [
        'title'       => _t('Max Avatar Width'),
        'description' => _t('Allowed image width, 0 for no limit'),
        'value'       => 512,
        'filter'      => 'int',
        'category'    => 'avatar',
    ],
    // Allowed height of avatar image, 0 for no limit
    'max_avatar_height' => [
        'title'       => _t('Max Avatar Height'),
        'description' => _t('Allowed image height, 0 for no limit'),
        'value'       => 512,
        'filter'      => 'int',
        'category'    => 'avatar',
    ],
    // Allowed width of avatar image file size, 0 for no limit
    'max_size'          => [
        'title'       => _t('Max File Size'),
        'description' => _t('Allowed avatar file to upload (in KB), 0 for no limit'),
        'value'       => 1024,
        'filter'      => 'int',
        'category'    => 'avatar',
    ],

    /*
    'avatar_extension'  => array(
        'title'         => _t('Format Supported'),
        'description'   => _t('Image extension allowed'),
        'value'         => 'jpg,gif,png,bmp',
        'category'      => 'avatar',
    ),
    */

    'path_tmp'    => [
        'title'       => _t('Temporary Path'),
        'description' => _t('For temporary storage of avatar'),
        'value'       => 'upload/user/tmp',
        'category'    => 'avatar',
    ],

    // OAuth
    'oauth_login' => [
        'category'    => 'oauth',
        'title'       => _a('oAuth login'),
        'description' => '',
        'edit'        => 'checkbox',
        'filter'      => 'number_int',
        'value'       => 0,
    ],

    'oauth_google' => [
        'category'    => 'oauth',
        'title'       => _a('Login by google'),
        'description' => '',
        'edit'        => 'checkbox',
        'filter'      => 'number_int',
        'value'       => 0,
    ],

    'oauth_google_client_id' => [
        'category' => 'oauth',
        'title'    => _a('Google client id'),
        'edit'     => 'text',
        'filter'   => 'string',
        'value'    => '',
    ],

    'oauth_google_client_secret' => [
        'category' => 'oauth',
        'title'    => _a('Google client secret'),
        'edit'     => 'text',
        'filter'   => 'string',
        'value'    => '',
    ],

    'oauth_twitter' => [
        'category'    => 'oauth',
        'title'       => _a('Login by twitter'),
        'description' => '',
        'edit'        => 'checkbox',
        'filter'      => 'number_int',
        'value'       => 0,
    ],

    'oauth_twitter_api_key' => [
        'category' => 'oauth',
        'title'    => _a('Twitter consumer Key (API Key)'),
        'edit'     => 'text',
        'filter'   => 'string',
        'value'    => '',
    ],

    'oauth_twitter_api_secret' => [
        'category' => 'oauth',
        'title'    => _a('Twitter consumer Secret (API Secret)'),
        'edit'     => 'text',
        'filter'   => 'string',
        'value'    => '',
    ],

    'oauth_facebook' => [
        'category'    => 'oauth',
        'title'       => _a('Login by facebook'),
        'description' => '',
        'edit'        => 'checkbox',
        'filter'      => 'number_int',
        'value'       => 0,
    ],

    'oauth_facebook_api_id' => [
        'category' => 'oauth',
        'title'    => _a('Facebook app ID'),
        'edit'     => 'text',
        'filter'   => 'string',
        'value'    => '',
    ],

    'oauth_facebook_api_secret' => [
        'category' => 'oauth',
        'title'    => _a('Facebook app Secret'),
        'edit'     => 'text',
        'filter'   => 'string',
        'value'    => '',
    ],

    'oauth_github' => [
        'category'    => 'oauth',
        'title'       => _a('Login by github'),
        'description' => '',
        'edit'        => 'checkbox',
        'filter'      => 'number_int',
        'value'       => 0,
    ],

    'oauth_github_client_id' => [
        'category' => 'oauth',
        'title'    => _a('Github client id'),
        'edit'     => 'text',
        'filter'   => 'string',
        'value'    => '',
    ],

    'oauth_github_client_secret' => [
        'category' => 'oauth',
        'title'    => _a('Github client secret'),
        'edit'     => 'text',
        'filter'   => 'string',
        'value'    => '',
    ],

    // Cron
    'module_cron' => [
        'category' => 'cron',
        'title' => _a('Active this module cron system'),
        'description' => '',
        'edit' => 'checkbox',
        'filter' => 'number_int',
        'value' => 1
    ],
    'cron_clean_session_days_after' => [
        'category' => 'cron',
        'title' => _a('Session timeout / in days'),
        'description' => 'Days count after expired session must be cleaned by cron job',
        'edit' => 'text',
        'filter' => 'number_int',
        'value' => 60
    ],
];

return [
    'category' => $category,
    'item'     => $config,
];