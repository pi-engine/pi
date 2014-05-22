<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * Preference specs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
$config = array();

// Config categories
$config['category'] = array(
    array(
        'name'      => 'general',
        'title'     => _t('General'),
    ),
    array(
        'name'      => 'meta',
        'title'     => _t('Head meta'),
    ),
    array(
        'name'      => 'intl',
        'title'     => _t('Internationalization'),
    ),
    array(
        'name'      => 'user',
        'title'     => _t('User account'),
    ),
    array(
        'name'      => 'admin',
        'title'     => _t('Admin login'),
    ),
    array(
        'name'      => 'text',
        'title'     => _t('Text processing'),
    ),
    array(
        'name'      => 'mail',
        'title'     => _t('Mailing'),
    ),
);

// Config items

// General section

$config['item'] = array(
    // General section

    'sitename'      => array(
        'title'         => _t('Site name'),
        'description'   => _t('Website name.'),
        'filter'        => 'string',
        'value'         => 'Pi Engine',
        'category'      => 'general',
    ),

    'slogan'        => array(
        'title'         => _t('Slogan'),
        'description'   => _t('Website slogan.'),
        'value'         => 'Power your web and mobile applications.',
        'category'      => 'general',
    ),

    'locale'        => array(
        'title'         => _t('Locale'),
        'description'   => _t('Locale for application content.'),
        'edit'          => 'locale',
        'value'         => 'en',
        'category'      => 'general',
    ),

    'charset'       => array(
        'title'         => _t('Charset'),
        'description'   => _t('Charset for page display.'),
        'value'         => 'utf-8',
        'category'      => 'general',
    ),

    'timezone'   => array(
        'title'         => _t('Timezone'),
        'description'   => _t('Timezone for application system.'),
        'edit'          => 'timezone',
        'category'      => 'general',
    ),

    'list_number'    => array(
        'title'         => _t('List count'),
        'description'   => _t('Number of items on a list.'),
        'value'         => '20',
        'filter'        => 'int',
        'category'      => 'general',
    ),

    'footer'        => array(
        'title'         => _t('Footer'),
        'description'   => _t('Content to be displayed on footer of front pages, HTML tags allowed.'),
        'edit'          => 'textarea',
        'category'      => 'general',
    ),

    'ga_account'   => array(
        'title'         => _t('GA account'),
        'description'   => _t('Google Analytics trackingID `UA-XXXXXXXX-X`. To specify host as well, append to the code `UA-XXXXXXXX-X; XXXX.tld`.'),
        'category'      => 'general',
    ),

    'foot_script'   => array(
        'title'         => _t('Foot scripts'),
        'description'   => _t('Scripts that will be appended to each page footer. Either naked or wrapped js scripts are allowed.'),
        'edit'          => 'textarea',
        'category'      => 'general',
    ),

    'theme'         => array(
        'title'         => _t('Theme'),
        'value'         => 'default',
        'category'      => 'general',
        'visible'       => 0,
    ),

    'theme_admin'   => array(
        'title'         => _t('Admin theme'),
        'value'         => 'default',
        'category'      => 'general',
        'visible'       => 0,
    ),

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

    'uname_format'  => array(
        'title'         => _t('Username format'),
        'description'   => _t('Format of username for registration.'),
        'edit'          => array(
            'type'      => 'select',
            'options'   => array(
                'options'       => array(
                    'strict'    => _t('Strict: alphabet or number only'),
                    'medium'    => _t('Medium: ASCII characters'),
                    'loose'     => _t('Loose: multi-byte characters'),
                ),
            ),
        ),
        'filter'        => 'string',
        'value'         => 'medium',
        'category'      => 'user',
    ),

    'uname_min'     => array(
        'title'         => _t('Minimum username'),
        'description'   => _t('Minimum length of username for user registration'),
        'value'         => 3,
        'filter'        => 'int',
        'category'      => 'user',
    ),

    'uname_max'     => array(
        'title'         => _t('Maximum username'),
        'description'   => _t('Maximum length of username for user registration'),
        'value'         => 32,
        'filter'        => 'int',
        'category'      => 'user',
    ),

    'password_min'  => array(
        'title'         => _t('Minimum password'),
        'description'   => _t('Minimum length of password for user registration'),
        'value'         => 5,
        'filter'        => 'int',
        'category'      => 'user',
    ),

    'password_max'  => array(
        'title'         => _t('Maximum password'),
        'description'   => _t('Maximum length of password for user registration'),
        'value'         => 32,
        'filter'        => 'int',
        'category'      => 'user',
    ),

    'uname_backlist'    => array(
        'title'         => _t('Username blacklist'),
        'description'   => _t('Reserved and forbidden username list. Separate each with a <strong>|</strong>, regexp syntax is allowed.'),
        'edit'          => 'textarea',
        'value'         => 'webmaster|^pi|^admin',
        'category'      => 'user',
    ),

    'email_backlist'    => array(
        'title'         => _t('Email blacklist'),
        'description'   => _t('Forbidden username list. Separate each with a <strong>|</strong>, regexp syntax is allowed.'),
        'edit'          => 'textarea',
        'value'         => 'pi-engine.org$',
        'category'      => 'user',
    ),

    'rememberme'        => array(
        'title'         => _t('Remember me'),
        'description'   => _t('Days to remember login, 0 for disable.'),
        'value'         => 14,
        'filter'        => 'int',
        'category'      => 'user',
    ),

    'login_field'      => array(
        'title'         => _t('Login field'),
        'description'   => _t('Identity field(s) for authentication.'),
        'edit'          => array(
            'type'      => 'select',
            'attributes'    => array(
                'multiple'  => true,
            ),
            'options'   => array(
                'value_options'   => array(
                    'identity'  => _t('Username'),
                    'email'     => _t('Email'),
                ),
            ),
        ),
        'filter'        => 'array',
        'value'         => array('identity'),
        'category'      => 'user',
    ),

    'login_attempts'      => array(
        'title'         => _t('Maximum attempts'),
        'description'   => _t('Maximum attempts allowed to try for user login'),
        'value'         => 5,
        'filter'        => 'int',
        'category'      => 'user',
    ),

    'login_disable'     => array(
        'title'         => _t('Login disable'),
        'description'   => _t('Disable user login'),
        'edit'          => 'checkbox',
        'value'         => 0,
        'filter'        => 'int',
        'category'      => 'user',
    ),

    'register_disable'  => array(
        'title'         => _t('Register disable'),
        'description'   => _t('Disable user registration'),
        'edit'          => 'checkbox',
        'value'         => 0,
        'filter'        => 'int',
        'category'      => 'user',
    ),

    'login_captcha'       => array(
        'title'         => _t('Login CAPTCHA'),
        'description'   => _t('Enable CAPTCHA for user login'),
        'edit'          => 'checkbox',
        'value'         => 0,
        'filter'        => 'int',
        'category'      => 'user',
    ),

    'register_captcha'  => array(
        'title'         => _t('Register CAPTCHA'),
        'description'   => _t('Enable CAPTCHA for user registration'),
        'edit'          => 'checkbox',
        'value'         => 1,
        'filter'        => 'int',
        'category'      => 'user',
    ),

    // Admin login section

    'admin_login_attempts'      => array(
        'title'         => _t('Maximum attempts'),
        'description'   => _t('Maximum attempts allowed to try for admin login'),
        'value'         => 5,
        'filter'        => 'int',
        'category'      => 'admin',
    ),

    'admin_rememberme'        => array(
        'title'         => _t('Remember me'),
        'description'   => _t('Days to remember login, 0 for disable.'),
        'value'         => 14,
        'filter'        => 'int',
        'category'      => 'admin',
    ),

    'admin_login_field'      => array(
        'title'         => _t('Login field'),
        'description'   => _t('Identity field(s) for admin authentication.'),
        'edit'          => array(
            'type'      => 'select',
            'attributes'    => array(
                'multiple'  => true,
            ),
            'options'   => array(
                'value_options'   => array(
                    'identity'  => _t('Username'),
                    'email'     => _t('Email'),
                ),
            ),
        ),
        'filter'        => 'array',
        'value'         => array('identity'),
        'category'      => 'admin',
    ),

    'admin_login_disable'     => array(
        'title'         => _t('Login disable'),
        'description'   => _t('Disable admin login'),
        'edit'          => 'checkbox',
        'value'         => 0,
        'filter'        => 'int',
        'category'      => 'admin',
    ),

    'admin_login_captcha'       => array(
        'title'         => _t('Login CAPTCHA'),
        'description'   => _t('Enable CAPTCHA for admin login'),
        'edit'          => 'checkbox',
        'value'         => 0,
        'filter'        => 'int',
        'category'      => 'admin',
    ),

    // Meta section

    'author'        => array(
        'title'         => '`author`',
        'description'   => _t('The author meta tag defines the name of the author of the document being read. Supported data formats include the name, email address of the webmaster, company name or URL.'),
        'edit'          => 'text',
        'value'         => 'Pi Engine',
        'category'      => 'meta',
    ),

    'generator'     => array(
        'title'         => '`generator`',
        'description'   => _t('Generator of the document being read.'),
        'edit'          => 'text',
        'value'         => 'Pi Engine',
        'category'      => 'meta',
    ),

    'keywords'      => array(
        'title'         => '`keywords`',
        'description'   => _t('The keywords meta tag is a series of keywords that represents the content of your site. Separated keywords by a comma.'),
        'edit'          => 'textarea',
        'value'         => 'Pi Engine,Web application,PHP,Zend Framework',
        'category'      => 'meta',
    ),

    'description'   => array(
        'title'         => '`description`',
        'description'   => _t('The description meta tag is a general description of what is contained in your web page'),
        'edit'          => 'textarea',
        'value'         => 'Pi Engine is an extensible development engine for web and mobile applications written in PHP.',
        'category'      => 'meta',
    ),

    // I18n section

    'number_style'    => array(
        'title'         => _t('Default number style'),
        'description'   => _t('See http://www.php.net/manual/en/class.numberformatter.php#intl.numberformatter-constants.unumberformatstyle'),
        'edit'          => array(
            'type'      => 'select',
            'options'   => array(
                'options'   => array(
                    'DEFAULT_STYLE'     => _t('Default format for the locale'),
                    'PATTERN_DECIMAL'   => _t('Decimal format defined by pattern'),
                    'DECIMAL'           => _t('Decimal format'),
                    'PERCENT'           => _t('Percent format'),
                    'SCIENTIFIC'        => _t('Scientific format'),
                    'SPELLOUT'          => _t('Spellout rule-based format'),
                    'ORDINAL'           => _t('Ordinal rule-based format'),
                    'DURATION'          => _t('Duration rule-based format'),
                    'PATTERN_RULEBASED' => _t('Rule-based format defined by pattern'),
                ),
            ),
        ),
        'value'         => 'DEFAULT_STYLE',
        'category'      => 'intl',
    ),

    'number_pattern'    => array(
        'title'         => _t('Default pattern for selected number style'),
        'description'   => _t('Only if required by style'),
        'edit'          => 'text',
        'value'         => '',
        'category'      => 'intl',
    ),

    'number_currency'   => array(
        'title'         => _t('Default currency type'),
        'description'   => _t('The 3-letter ISO 4217 currency code indicating the currency to use.'),
        'edit'          => 'text',
        'value'         => '',
        'category'      => 'intl',
    ),

    'date_calendar'     => array(
        'title'         => _t('Default calendar for the locale'),
        'description'   => _t('"persian" is suggested for Persian language. See http://www.php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants.calendartypes'),
        'edit'          => 'text',
        'value'         => '',
        'category'      => 'intl',
    ),

    'date_datetype'    => array(
        'title'         => _t('Default date type'),
        'description'   => _t('See http://www.php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants'),
        'edit'          => array(
            'type'      => 'select',
            'options'   => array(
                'options'   => array(
                    'NONE'      => _t('Do not include this element'),
                    'FULL'      => _t('Completely specified style (Tuesday, April 12, 1952 AD or 3:30:42pm PST)'),
                    'LONG'      => _t('Long style (January 12, 1952 or 3:30:32pm)'),
                    'MEDIUM'    => _t('Medium style (Jan 12, 1952)'),
                    'SHORT'     => _t('Most abbreviated style, only essential data (12/13/52 or 3:30pm)'),
                ),
            ),
        ),
        'value'         => 'MEDIUM',
        'category'      => 'intl',
    ),

    'date_timetype'    => array(
        'title'         => _t('Default time type'),
        'description'   => _t('See http://www.php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants'),
        'edit'          => array(
            'type'      => 'select',
            'options'   => array(
                'options'   => array(
                    'NONE'      => _t('Do not include this element'),
                    'FULL'      => _t('Completely specified style (Tuesday, April 12, 1952 AD or 3:30:42pm PST)'),
                    'LONG'      => _t('Long style (January 12, 1952 or 3:30:32pm)'),
                    'MEDIUM'    => _t('Medium style (Jan 12, 1952)'),
                    'SHORT'     => _t('Most abbreviated style, only essential data (12/13/52 or 3:30pm)'),
                ),
            ),
        ),
        'value'         => 'LONG',
        'category'      => 'intl',
    ),

    'date_pattern'      => array(
        'title'         => _t('Default formatting pattern for date-time'),
        'description'   => _t('See http://userguide.icu-project.org/formatparse/datetime'),
        'edit'          => 'text',
        'value'         => 'yyyy-MM-dd HH:mm:ss',
        'category'      => 'intl',
    ),

    'date_format'       => array(
        'title'         => _t('Default format for legacy date function'),
        'description'   => _t('The format is required in case Intl extension is not available. See http://www.php.net/manual/en/function.date.php'),
        'edit'          => 'text',
        'value'         => 'Y-m-d H:i:s',
        'category'      => 'intl',
    ),

    // Mailing section

    'adminmail'     => array(
        'title'         => _t('Admin email'),
        'description'   => _t('Admin email address for contact.'),
        'filter'        => 'email',
        'category'      => 'mail',
    ),

    'adminname'      => array(
        'title'         => _t('Admin name'),
        'description'   => _t('User name used to send emails'),
        'category'      => 'mail',
    ),

    'mail_encoding'       => array(
        'title'         => _t('Email encoding'),
        'description'   => _t('Encoding for email contents'),
        'value'         => 'UTF-8',
        'category'      => 'mail',
    ),

    // Text processing

    'editor'        => array(
        'title'         => _t('Text editor'),
        'description'   => _t('Default editor for text processing'),
        'category'      => 'text',
    ),

    'censor_enable'  => array(
        'title'         => _t('Enable word censoring'),
        'description'   => _t('Words will be censored if this option is enabled. This option may be turned off for enhanced site speed.'),
        'edit'          => 'checkbox',
        'value'         => 0,
        'filter'        => 'int',
        'category'      => 'text',
    ),

    'censor_words'  => array(
        'title'         => _t('Words to censor'),
        'description'   => _t('Enter words that should be censored in user posts. Separate each with a "|", case insensitive.'),
        'edit'          => 'textarea',
        'value'         => 'fuck|shit',
        'category'      => 'text',
    ),

    'censor_replace'    => array(
        'title'         => _t('Word to replace censored words'),
        'description'   => _t('Censored words will be replaced with the characters entered in this textbox'),
        'value'         => '#OOPS#',
        'category'      => 'text',
    ),

    // Orphan configs, not displayed in preference edit page
    'theme_module'      => array(
        'title'         => _t('Module themes'),
        'description'   => _t('Themes for modules.'),
        'value'         => array(),
        'filter'        => 'array',
        'category'      => '',
        'visible'       => 0,
    ),

    'nav_front'         => array(
        'title'         => _t('Front navigation'),
        'description'   => _t('Global navigation for front end.'),
        'value'         => 'front',
        'category'      => '',
        'visible'       => 0,
    ),

    'nav_admin'         => array(
        'title'         => _t('Admin navigation'),
        'description'   => _t('Global navigation for admin.'),
        'value'         => 'admin',
        'category'      => '',
        'visible'       => 0,
    ),

);

return $config;
