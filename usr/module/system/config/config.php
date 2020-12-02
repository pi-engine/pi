<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

/**
 * Preference specs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
$config = [];

// Config categories
$config['category'] = [
    [
        'name'  => 'general',
        'title' => _t('General'),
    ],
    [
        'name'  => 'head_meta',
        'title' => _t('Head meta'),
    ],
    [
        'name'  => 'head_meta_extra',
        'title' => _t('Extra head meta'),
    ],
    [
        'name'  => 'geo_tag',
        'title' => _t('Geo tags and setting'),
    ],
    [
        'name'  => 'google',
        'title' => _t('Google'),
    ],
    [
        'name'  => 'osm',
        'title' => _t('Openstreetmap'),
    ],
    [
        'name'  => 'intl',
        'title' => _t('Internationalization'),
    ],
    [
        'name'  => 'user',
        'title' => _t('User account'),
    ],
    [
        'name'  => 'admin',
        'title' => _t('Admin login'),
    ],
    [
        'name'  => 'text',
        'title' => _t('Text processing'),
    ],
    [
        'name'  => 'mail',
        'title' => _t('Mailing'),
    ],
];

// Config items

// General section

$config['item'] = [
    // General section

    'sitename' => [
        'title'       => _t('Site name'),
        'description' => _t('Website name.'),
        'filter'      => 'string',
        'value'       => 'Pi Engine',
        'category'    => 'general',
    ],

    'slogan' => [
        'title'       => _t('Slogan'),
        'description' => _t('Website slogan.'),
        'value'       => 'Power your web and mobile applications.',
        'category'    => 'general',
    ],

    'locale' => [
        'title'       => _t('Locale'),
        'description' => _t('Locale for application content.'),
        'edit'        => 'locale',
        'value'       => 'en',
        'category'    => 'general',
    ],

    'charset' => [
        'title'       => _t('Charset'),
        'description' => _t('Charset for page display.'),
        'value'       => 'utf-8',
        'category'    => 'general',
    ],

    'timezone' => [
        'title'       => _t('Timezone'),
        'description' => _t('Timezone for application system.'),
        'edit'        => 'timezone',
        'category'    => 'general',
    ],

    'list_number' => [
        'title'       => _t('List count'),
        'description' => _t('Number of items on a list.'),
        'value'       => '20',
        'filter'      => 'int',
        'category'    => 'general',
    ],

    'footer' => [
        'title'       => _t('Footer'),
        'description' => _t('Content to be displayed on footer of front pages, HTML tags allowed.'),
        'edit'        => 'textarea',
        'category'    => 'general',
    ],

    'foot_script' => [
        'title'       => _t('Foot scripts'),
        'description' => _t('Scripts that will be appended to each page footer. Either naked or wrapped js scripts are allowed.'),
        'edit'        => 'textarea',
        'category'    => 'general',
    ],

    'compile_css' => [
        'title'    => _t('Compile CSS'),
        'edit'     => 'checkbox',
        'value'    => 0,
        'filter'   => 'int',
        'category' => 'general',
    ],

    'compile_js' => [
        'title'    => _t('Compile JS'),
        'edit'     => 'checkbox',
        'value'    => 0,
        'filter'   => 'int',
        'category' => 'general',
    ],

    'minify_html_output' => [
        'title'    => _t('Minify HTML output'),
        'edit'     => 'checkbox',
        'value'    => 0,
        'filter'   => 'int',
        'category' => 'general',
    ],

    'theme' => [
        'title'    => _t('Theme'),
        'value'    => 'default',
        'category' => 'general',
        'visible'  => 0,
    ],

    'theme_admin' => [
        'title'    => _t('Admin theme'),
        'value'    => 'default',
        'category' => 'general',
        'visible'  => 0,
    ],

    /*
    'environment'    => array(
        'title'         => _t('Run environment'),
        'description'   => _t('Will override setting in `var/config/engine.php`.'),
        'edit'          => array(
            'type'      => 'select',
            'options'   => array(
                'options'   => array(
                    'production'        => _t('Production'),
                    'development'       => _t('Development'),
                    'test'              => _t('QA test'),
                ),
            ),
        ),
        'value'         => 'development',
        'category'      => 'general',
    ),
    */

    // User account

    'uname_format' => [
        'title'       => _t('Username format'),
        'description' => _t('Format of username for registration.'),
        'edit'        => [
            'type'    => 'select',
            'options' => [
                'options' => [
                    'strict'       => _t('Strict: alphabet or number only'),
                    'strict-space' => _t('Strict/space: alphabet, number or space only'),
                    'medium'       => _t('Medium: ASCII characters'),
                    'medium-space' => _t('Medium/space: ASCII characters and spaces'),
                    'loose'        => _t('Loose: multi-byte characters'),
                    'loose-space'  => _t('Loose/space: multi-byte characters and spaces'),
                ],
            ],
        ],
        'filter'      => 'string',
        'value'       => 'medium',
        'category'    => 'user',
    ],

    'uname_min' => [
        'title'       => _t('Minimum username'),
        'description' => _t('Minimum length of username for user registration'),
        'value'       => 3,
        'filter'      => 'int',
        'category'    => 'user',
    ],

    'uname_max' => [
        'title'       => _t('Maximum username'),
        'description' => _t('Maximum length of username for user registration'),
        'value'       => 32,
        'filter'      => 'int',
        'category'    => 'user',
    ],

    'password_min' => [
        'title'       => _t('Minimum password'),
        'description' => _t('Minimum length of password for user registration'),
        'value'       => 5,
        'filter'      => 'int',
        'category'    => 'user',
    ],

    'password_max' => [
        'title'       => _t('Maximum password'),
        'description' => _t('Maximum length of password for user registration'),
        'value'       => 32,
        'filter'      => 'int',
        'category'    => 'user',
    ],

    'uname_blacklist' => [
        'title'       => _t('Username blacklist'),
        'description' => _t('Reserved and forbidden username list. Separate each with a <strong>|</strong>, regexp syntax is allowed.'),
        'edit'        => 'textarea',
        'value'       => 'webmaster|^pi|^admin',
        'category'    => 'user',
    ],

    'email_blacklist' => [
        'title'       => _t('Email blacklist'),
        'description' => _t('Forbidden username list. Separate each with a <strong>|</strong>, regexp syntax is allowed.'),
        'edit'        => 'textarea',
        'value'       => 'pi-engine.org$',
        'category'    => 'user',
    ],

    'rememberme' => [
        'title'       => _t('Remember me'),
        'description' => _t('Days to remember login, 0 for disable.'),
        'value'       => 14,
        'filter'      => 'int',
        'category'    => 'user',
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
        'category'    => 'user',
    ],

    'login_attempts' => [
        'title'       => _t('Maximum attempts'),
        'description' => _t('Maximum attempts allowed to try for user login'),
        'value'       => 5,
        'filter'      => 'int',
        'category'    => 'user',
    ],

    'login_disable' => [
        'title'       => _t('Login disable'),
        'description' => _t('Disable user login'),
        'edit'        => 'checkbox',
        'value'       => 0,
        'filter'      => 'int',
        'category'    => 'user',
    ],

    'register_disable' => [
        'title'       => _t('Register disable'),
        'description' => _t('Disable user registration'),
        'edit'        => 'checkbox',
        'value'       => 0,
        'filter'      => 'int',
        'category'    => 'user',
    ],

    'login_captcha' => [
        'title'       => _t('Login CAPTCHA'),
        'description' => _t('Enable CAPTCHA for user login'),
        'edit'        => 'checkbox',
        'value'       => 0,
        'filter'      => 'int',
        'category'    => 'user',
    ],

    'register_captcha' => [
        'title'       => _t('Register CAPTCHA'),
        'description' => _t('Enable CAPTCHA for user registration'),
        'edit'        => 'checkbox',
        'value'       => 1,
        'filter'      => 'int',
        'category'    => 'user',
    ],

    // Admin login section

    'admin_login_attempts' => [
        'title'       => _t('Maximum attempts'),
        'description' => _t('Maximum attempts allowed to try for admin login'),
        'value'       => 5,
        'filter'      => 'int',
        'category'    => 'admin',
    ],

    'admin_rememberme' => [
        'title'       => _t('Remember me'),
        'description' => _t('Days to remember login, 0 for disable.'),
        'value'       => 14,
        'filter'      => 'int',
        'category'    => 'admin',
    ],

    'admin_login_field' => [
        'title'       => _t('Login field'),
        'description' => _t('Identity field(s) for admin authentication.'),
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
        'category'    => 'admin',
    ],

    'admin_login_disable' => [
        'title'       => _t('Login disable'),
        'description' => _t('Disable admin login'),
        'edit'        => 'checkbox',
        'value'       => 0,
        'filter'      => 'int',
        'category'    => 'admin',
    ],

    'admin_login_captcha' => [
        'title'       => _t('Login CAPTCHA'),
        'description' => _t('Enable CAPTCHA for admin login'),
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
        'category'    => 'admin',
    ],

    // Meta section

    /*
    'author'        => array(
        'title'         => '`author`',
        'description'   => _t('The author meta tag defines the name of the author of the document being read. Supported data formats include the name, email address of the webmaster, company name or URL.'),
        'edit'          => 'text',
        'value'         => 'Pi Engine',
        'category'      => 'head_meta',
    ),

    'generator'     => array(
        'title'         => '`generator`',
        'description'   => _t('Generator of the document being read.'),
        'edit'          => 'text',
        'value'         => 'Pi Engine',
        'category'      => 'head_meta',
    ),
    */

    'head_title' => [
        'title'       => '`title`',
        'description' => _t('Head title for web page'),
        'edit'        => 'text',
        'value'       => 'A multi-tenant application development engine for cloud ready SaaS platform.',
        'category'    => 'head_meta',
    ],

    'keywords' => [
        'title'       => '`keywords`',
        'description' => _t('The keywords meta tag is a series of keywords that represents the content of your site. Separated keywords by a comma.'),
        'edit'        => 'textarea',
        'value'       => 'Pi Engine,Web application,SaaS,Multi-tenant,PHP,Laminas Framework',
        'category'    => 'head_meta',
    ],

    'description' => [
        'title'       => '`description`',
        'description' => _t('The description meta tag is a general description of what is contained in your web page'),
        'edit'        => 'textarea',
        'value'       => 'Pi Engine is an open source project developed in PHP and MySQL upon frameworks including Laminas Framework, jQuery, AngularJS, Bootstrap as well as icons by FontAwesome.',
        'category'    => 'head_meta',
    ],

    // I18n section

    'number_style' => [
        'title'       => _t('Default number style'),
        'description' => _t('See http://www.php.net/manual/en/class.numberformatter.php#intl.numberformatter-constants.unumberformatstyle'),
        'edit'        => [
            'type'    => 'select',
            'options' => [
                'options' => [
                    'DEFAULT_STYLE'     => _t('Default format for the locale'),
                    'PATTERN_DECIMAL'   => _t('Decimal format defined by pattern'),
                    'DECIMAL'           => _t('Decimal format'),
                    'PERCENT'           => _t('Percent format'),
                    'SCIENTIFIC'        => _t('Scientific format'),
                    'SPELLOUT'          => _t('Spellout rule-based format'),
                    'ORDINAL'           => _t('Ordinal rule-based format'),
                    'DURATION'          => _t('Duration rule-based format'),
                    'PATTERN_RULEBASED' => _t('Rule-based format defined by pattern'),
                ],
            ],
        ],
        'value'       => 'DEFAULT_STYLE',
        'category'    => 'intl',
    ],

    'number_pattern' => [
        'title'       => _t('Default pattern for selected number style'),
        'description' => _t('Only if required by style'),
        'edit'        => 'text',
        'value'       => '',
        'category'    => 'intl',
    ],

    'number_currency' => [
        'title'       => _t('Default currency type'),
        'description' => _t('The 3-letter ISO 4217 currency code indicating the currency to use.'),
        'edit'        => 'text',
        'value'       => '',
        'category'    => 'intl',
    ],

    'date_calendar' => [
        'title'       => _t('Default calendar for the locale'),
        'description' => _t(
            '"persian" is suggested for Persian language. See http://www.php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants.calendartypes'
        ),
        'edit'        => 'text',
        'value'       => '',
        'category'    => 'intl',
    ],

    'date_datetype' => [
        'title'       => _t('Default date type'),
        'description' => _t('See http://www.php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants'),
        'edit'        => [
            'type'    => 'select',
            'options' => [
                'options' => [
                    'NONE'   => _t('Do not include this element'),
                    'FULL'   => _t('Completely specified style (Tuesday, April 12, 1952 AD or 3:30:42pm PST)'),
                    'LONG'   => _t('Long style (January 12, 1952 or 3:30:32pm)'),
                    'MEDIUM' => _t('Medium style (Jan 12, 1952)'),
                    'SHORT'  => _t('Most abbreviated style, only essential data (12/13/52 or 3:30pm)'),
                ],
            ],
        ],
        'value'       => 'MEDIUM',
        'category'    => 'intl',
    ],

    'date_timetype' => [
        'title'       => _t('Default time type'),
        'description' => _t('See http://www.php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants'),
        'edit'        => [
            'type'    => 'select',
            'options' => [
                'options' => [
                    'NONE'   => _t('Do not include this element'),
                    'FULL'   => _t('Completely specified style (Tuesday, April 12, 1952 AD or 3:30:42pm PST)'),
                    'LONG'   => _t('Long style (January 12, 1952 or 3:30:32pm)'),
                    'MEDIUM' => _t('Medium style (Jan 12, 1952)'),
                    'SHORT'  => _t('Most abbreviated style, only essential data (12/13/52 or 3:30pm)'),
                ],
            ],
        ],
        'value'       => 'LONG',
        'category'    => 'intl',
    ],

    'date_pattern' => [
        'title'       => _t('Default formatting pattern for date-time'),
        'description' => _t('See http://userguide.icu-project.org/formatparse/datetime'),
        'edit'        => 'text',
        'value'       => 'yyyy-MM-dd HH:mm:ss',
        'category'    => 'intl',
    ],

    'date_format' => [
        'title'       => _t('Default format for legacy date function'),
        'description' => _t('The format is required in case Intl extension is not available. See http://www.php.net/manual/en/function.date.php'),
        'edit'        => 'text',
        'value'       => 'Y-m-d H:i:s',
        'category'    => 'intl',
    ],

    // Mailing section

    'adminmail' => [
        'title'       => _t('Admin email'),
        'description' => _t('Admin email address for contact.'),
        'filter'      => 'email',
        'category'    => 'mail',
    ],

    'adminname' => [
        'title'       => _t('Admin name'),
        'description' => _t('User name used to send emails'),
        'category'    => 'mail',
    ],

    'mail_encoding' => [
        'title'       => _t('Email encoding'),
        'description' => _t('Encoding for email contents'),
        'value'       => 'UTF-8',
        'category'    => 'mail',
    ],

    'site_mail_footer' => [
        'title'       => _t('Mail footer'),
        'description' => _t(
            'Content to be displayed on email footer, HTML tags allowed. Usefull to diplay Android/iOS links, sign with Official Company Address/Name or logo,etc.'
        ),
        'edit'        => 'textarea',
        'category'    => 'mail',
    ],

    // Text processing

    'editor' => [
        'title'       => _t('Text editor'),
        'description' => _t('Default editor for text processing'),
        'edit'        => 'editor_select',
        'category'    => 'text',
    ],

    'censor_enable' => [
        'title'       => _t('Enable word censoring'),
        'description' => _t('Words will be censored if this option is enabled. This option may be turned off for enhanced site speed.'),
        'edit'        => 'checkbox',
        'value'       => 0,
        'filter'      => 'int',
        'category'    => 'text',
    ],

    'censor_words' => [
        'title'       => _t('Words to censor'),
        'description' => _t('Enter words that should be censored in user posts. Separate each with a "|", case insensitive.'),
        'edit'        => 'textarea',
        'value'       => 'fuck|shit',
        'category'    => 'text',
    ],

    'censor_replace' => [
        'title'       => _t('Word to replace censored words'),
        'description' => _t('Censored words will be replaced with the characters entered in this textbox'),
        'value'       => '#OOPS#',
        'category'    => 'text',
    ],

    // Orphan configs, not displayed in preference edit page

    'theme_module' => [
        'title'       => _t('Module themes'),
        'description' => _t('Themes for modules.'),
        'value'       => [],
        'filter'      => 'array',
        'category'    => '',
        'visible'     => 0,
    ],

    'nav_front' => [
        'title'       => _t('Front navigation'),
        'description' => _t('Global navigation for front end.'),
        'value'       => 'front',
        'category'    => '',
        'visible'     => 0,
    ],

    'nav_admin' => [
        'title'       => _t('Admin navigation'),
        'description' => _t('Global navigation for admin.'),
        'value'       => 'admin',
        'category'    => '',
        'visible'     => 0,
    ],

    'title_type' => [
        'title'       => _t('Meta title type'),
        'description' => _t('Set short or lang meta title type'),
        'edit'        => [
            'type'    => 'select',
            'options' => [
                'options' => [
                    1 => _t('Postfix by page - module - website titles'),
                    2 => _t('Postfix by page - website titles'),
                    3 => _t('Prefix by website - module - page titles'),
                    4 => _t('Prefix by website - page titles'),
                    5 => _t('Just page title'),
                ],
            ],
        ],
        'filter'      => 'int',
        'value'       => 1,
        'category'    => 'head_meta_extra',
    ],

    'author' => [
        'title'       => '`author`',
        'description' => _t(
            'The author meta tag defines the name of the author of the document being read. Supported data formats include the name, email address of the webmaster, company name or URL.'
        ),
        'edit'        => 'text',
        'value'       => 'Pi Engine',
        'category'    => 'head_meta_extra',
    ],

    'generator' => [
        'title'       => '`generator`',
        'description' => _t('Generator of the document being read.'),
        'edit'        => 'text',
        'value'       => 'Pi Engine',
        'category'    => 'head_meta_extra',
    ],

    'og_local' => [
        'title'       => _t('Website Open Graph locale'),
        'description' => _t('Set website OG locale, like en_GB'),
        'edit'        => 'text',
        'value'       => 'en_GB',
        'category'    => 'head_meta_extra',
    ],

    'twitter_account' => [
        'title'       => _t('Twitter account'),
        'description' => _t('Username for the website used in the Twitter card. Add @ before username like @PiEnable or leave it empty'),
        'edit'        => 'text',
        'category'    => 'head_meta_extra',
    ],

    'facebook_appid' => [
        'title'       => _t('Facebook app id'),
        'description' => _t('Set facebook app id here for use open graph tags on facebook page, its your website facebook page id'),
        'edit'        => 'text',
        'category'    => 'head_meta_extra',
    ],

    'pinterest_verify' => [
        'title'       => _t('Pinterest domain verify'),
        'description' => _t('Set pinterest domain verify code here, if your website have page on pinterest'),
        'edit'        => 'text',
        'category'    => 'head_meta_extra',
    ],

    // Geo tags and setting

    'geo_latitude' => [
        'title'       => _t('Latitude'),
        'description' => _t('Global latitude on system'),
        'edit'        => 'text',
        'category'    => 'geo_tag',
    ],

    'geo_longitude' => [
        'title'       => _t('Longitude'),
        'description' => _t('Global longitude on system'),
        'edit'        => 'text',
        'category'    => 'geo_tag',
    ],

    'geo_placename' => [
        'title'       => _t('Placename'),
        'description' => _t('Global place name and map title on system'),
        'edit'        => 'text',
        'category'    => 'geo_tag',
    ],

    'geo_region' => [
        'title'       => _t('Region'),
        'description' => _t('For example IR-07 for Iran Tehran'),
        'edit'        => 'text',
        'category'    => 'geo_tag',
    ],

    'map_type' => [
        'title'       => _t('Map type'),
        'description' => _t('Global setting for use google map or Openstreetmap'),
        'edit'        => [
            'type'    => 'select',
            'options' => [
                'options' => [
                    'google' => _t('Google map'),
                    'osm'    => _t('Openstreetmap'),
                ],
            ],
        ],
        'value'       => 'google',
        'category'    => 'geo_tag',
    ],

    // Google setting

    'ga_account' => [
        'title'       => _t('GA account'),
        'description' => _t('Google Analytics trackingID `UA-XXXXXXXX-X`. To specify host as well, append to the code `UA-XXXXXXXX-X; XXXX.tld`.'),
        'edit'        => 'text',
        'value'       => '',
        'filter'      => 'string',
        'category'    => 'google',
    ],

    'google_map_key' => [
        'title'       => _t('Google map key'),
        'description' => _t('Key for google map, get it from https://developers.google.com/maps/documentation/javascript/get-api-key'),
        'edit'        => 'text',
        'value'       => '',
        'filter'      => 'string',
        'category'    => 'google',
    ],

    'captcha_public_key' => [
        'title'       => _t('ReCaptcha Site key'),
        'description' => _t('see https://www.google.com/recaptcha to create your public key dedicated to your website domain. Both keys are mandatory.'),
        'edit'        => 'text',
        'value'       => '',
        'filter'      => 'string',
        'category'    => 'google',
    ],

    'captcha_private_key' => [
        'title'       => _t('ReCaptcha Secret key'),
        'description' => _t('see https://www.google.com/recaptcha to create your private key dedicated to your website domain. Both keys are mandatory..'),
        'edit'        => 'text',
        'value'       => '',
        'filter'      => 'string',
        'category'    => 'google',
    ],

    // Openstreetmap setting

    'osm_tile' => [
        'title'       => _t('Openstreetmap tile url'),
        'description' => _t('Setup osm tile on your own server or use mapbox or openstreetmap servers'),
        'edit'        => 'text',
        'value'       => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        'filter'      => 'string',
        'category'    => 'osm',
    ],

    'osm_route' => [
        'title'       => _t('Openstreetmap route url'),
        'description' => _t('Setup osrm route project on your own server or use mapbox'),
        'edit'        => 'text',
        'value'       => 'https://router.project-osrm.org/route/v1',
        'filter'      => 'string',
        'category'    => 'osm',
    ],
];

return $config;