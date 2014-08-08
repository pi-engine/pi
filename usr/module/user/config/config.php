<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

/**
 * Config specs
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */

$category = array(
    /*
    array(
        'name'  => 'general',
        'title' => _t('General'),
    ),
    */
    array(
        'name'      => 'login',
        'title'     => _t('Login'),
    ),
    array(
        'name'      => 'register',
        'title'     => _t('Registration'),
    ),
    array(
        'name'      => 'account',
        'title'     => _t('Account'),
    ),
    array(
        'name'  => 'avatar',
        'title' => _t('Avatar'),
    ),
);

$config = array(
    // General
    'list_limit' => array(
        'title'         => _t('List limit'),
        'description'   => _t('Number of items on list page.'),
        'value'         => 20,
        'filter'        => 'int',
    ),

    'birthdate_format'  => array(
        'title'         => _t('Birthdate format'),
        'description'   => _t('Format for birthdate display.'),
        'value'         => 'Y-m-d',
    ),

    'email_expiration' => array(
        'title'         => _t('Email expiration'),
        'description'   => _t('Expiration time for email/password reset (in hours).'),
        'value'         => 24,
        'filter'        => 'int',
    ),

    'email_confirm'     => array(
        'title'         => _t('Email confirmation'),
        'description'   => _t('Email confirmation with token is required for email change.'),
        'edit'          => 'checkbox',
        'value'         => 1,
        'filter'        => 'int',
    ),

    'require_profile_complete' => array(
        'title'         => _t('Profile complete'),
        'description'   => _t('Require user to complete profile data.'),
        'edit'          => 'checkbox',
        'value'         => 0,
        'filter'        => 'int',
    ),

    'head_keywords'     => array(
        'title'         => _t('Head keywords'),
        'description'   => _t('Head keywords for SEO.'),
        'value'         => _t('account,social,tools,privacy,settings,profile,user,login,register,password,avatar'),
    ),

    // Login
    'login_disable'     => array(
        'title'         => _t('Login disable'),
        'description'   => _t('Disable user login'),
        'edit'          => 'checkbox',
        'value'         => 0,
        'filter'        => 'int',
        'category'      => 'login',
    ),

    'login_captcha'       => array(
        'title'         => _t('Login CAPTCHA'),
        'description'   => _t('Enable CAPTCHA for user login'),
        'edit'          => 'checkbox',
        'value'         => 0,
        'filter'        => 'int',
        'category'      => 'login',
    ),

    'rememberme'        => array(
        'title'         => _t('Remember me'),
        'description'   => _t('Days to remember login, 0 for disable.'),
        'value'         => 14,
        'filter'        => 'int',
        'category'      => 'login',
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
        'category'      => 'login',
    ),

    'login_attempts'      => array(
        'title'         => _t('Maximum attempts'),
        'description'   => _t('Maximum attempts allowed to try for user login'),
        'value'         => 5,
        'filter'        => 'int',
        'category'      => 'login',
    ),

    // Register
    'register_disable'  => array(
        'title'         => _t('Register disable'),
        'description'   => _t('Disable user registration'),
        'edit'          => 'checkbox',
        'value'         => 0,
        'filter'        => 'int',
        'category'      => 'register',
    ),

    'register_captcha'  => array(
        'title'         => _t('Register CAPTCHA'),
        'description'   => _t('Enable CAPTCHA for user registration'),
        'edit'          => 'checkbox',
        'value'         => 1,
        'filter'        => 'int',
        'category'      => 'register',
    ),

    'require_register_complete' => array(
        'title'         => _t('Register complete'),
        'description'   => _t('Require user to complete register data in an extra form.'),
        'edit'          => 'checkbox',
        'value'         => 0,
        'filter'        => 'int',
        'category'      => 'register',
    ),

    'register_notification' => array(
        'title'         => _t('Email notification'),
        'description'   => _t('Send email notification for register success.'),
        'edit'          => 'checkbox',
        'value'         => 0,
        'filter'        => 'int',
        'category'      => 'register',
    ),

    'register_activation'  => array(
        'title'         => _t('Activation'),
        'description'   => _t('Activation mode for user accounts'),
        'edit'          => array(
            'type'      => 'select',
            'options'   => array(
                'options'       => array(
                    'auto'      => _t('Automatically activated'),
                    'email'     => _t('Activated by user email'),
                    'approval'  => _t('Activated by admin approval'),
                ),
            ),
        ),
        'filter'        => 'string',
        'value'         => 'email',
        'category'      => 'register',
    ),

    'activation_expiration' => array(
        'title'         => _t('Activation expiration'),
        'description'   => _t('Expiration time for activation email (in hours).'),
        'value'         => 24,
        'filter'        => 'int',
        'category'      => 'register',
    ),

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
        'category'      => 'account',
    ),

    'uname_min'     => array(
        'title'         => _t('Minimum username'),
        'description'   => _t('Minimum length of username for user registration'),
        'value'         => 3,
        'filter'        => 'int',
        'category'      => 'account',
    ),

    'uname_max'     => array(
        'title'         => _t('Maximum username'),
        'description'   => _t('Maximum length of username for user registration'),
        'value'         => 32,
        'filter'        => 'int',
        'category'      => 'account',
    ),
    'name_format'  => array(
        'title'         => _t('Display name format'),
        'description'   => _t('Format of display name for registration.'),
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
        'value'         => 'loose',
        'category'      => 'account',
    ),

    'name_min'     => array(
        'title'         => _t('Minimum display name'),
        'description'   => _t('Minimum length of display name for user registration'),
        'value'         => 3,
        'filter'        => 'int',
        'category'      => 'account',
    ),

    'name_max'     => array(
        'title'         => _t('Maximum display name'),
        'description'   => _t('Maximum length of display name for user registration'),
        'value'         => 32,
        'filter'        => 'int',
        'category'      => 'account',
    ),

    'password_min'  => array(
        'title'         => _t('Minimum password'),
        'description'   => _t('Minimum length of password for user registration'),
        'value'         => 5,
        'filter'        => 'int',
        'category'      => 'account',
    ),

    'password_max'  => array(
        'title'         => _t('Maximum password'),
        'description'   => _t('Maximum length of password for user registration'),
        'value'         => 32,
        'filter'        => 'int',
        'category'      => 'account',
    ),

    'uname_backlist'    => array(
        'title'         => _t('Username blacklist'),
        'description'   => _t('Reserved and forbidden username list, separated with `|`, regexp syntax is allowed.'),
        'edit'          => 'textarea',
        'value'         => 'webmaster|^pi|^admin',
        'category'      => 'account',
    ),

    'name_backlist'    => array(
        'title'         => _t('Display blacklist'),
        'description'   => _t('Reserved and forbidden display name list, separated with `|`, regexp syntax is allowed.'),
        'edit'          => 'textarea',
        'value'         => 'webmaster|^pi|^admin',
        'category'      => 'account',
    ),

    'email_backlist'    => array(
        'title'         => _t('Email blacklist'),
        'description'   => _t('Forbidden email list, separated with `|`, regexp syntax is allowed.'),
        'edit'          => 'textarea',
        'value'         => 'pi-engine.org$',
        'category'      => 'account',
    ),

    // Avatar
    // Allowed width of avatar image, 0 for no limit
    'max_avatar_width'  => array(
        'title'         => _t('Max Avatar Width'),
        'description'   => _t('Allowed image width, 0 for no limit'),
        'value'         => 2048,
        'filter'        => 'int',
        'category'      => 'avatar',
    ),
    // Allowed height of avatar image, 0 for no limit
    'max_avatar_height' => array(
        'title'         => _t('Max Avatar Height'),
        'description'   => _t('Allowed image height, 0 for no limit'),
        'value'         => 2048,
        'filter'        => 'int',
        'category'      => 'avatar',
    ),
    // Allowed width of avatar image file size, 0 for no limit
    'max_size'          => array(
        'title'         => _t('Max File Size'),
        'description'   => _t('Allowed avatar file to upload (in KB), 0 for no limit'),
        'value'         => 1024,
        'filter'        => 'int',
        'category'      => 'avatar',
    ),

    /*
    'avatar_extension'  => array(
        'title'         => _t('Format Supported'),
        'description'   => _t('Image extension allowed'),
        'value'         => 'jpg,gif,png,bmp',
        'category'      => 'avatar',
    ),
    */
    
    'path_tmp'          => array(
        'title'         => _t('Temporary Path'),
        'description'   => _t('For temporary storage of avatar'),
        'value'         => 'upload/user/tmp',
        'category'      => 'avatar',
    ),
);

return array(
    'category'  => $category,
    'item'      => $config,
);