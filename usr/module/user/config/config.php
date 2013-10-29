<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * Config specs
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */

$category = array(
    array(
        'name'  => 'general',
        'title' => _t('General'),
    ),
    array(
        'name'  => 'avatar',
        'title' => _t('Avatar'),
    ),
    array(
        'name'      => 'account',
        'title'     => _t('User account'),
    ),
);

$config = array(
    // General
    'list_limit' => array(
        'title'     => _t('Number of items on list page.'),
        'value'         => 10,
        'filter'        => 'int',
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
        'filter'        => 'number_int',
        'category'      => 'account',
    ),

    'uname_max'     => array(
        'title'         => _t('Maximum username'),
        'description'   => _t('Maximum length of username for user registration'),
        'value'         => 32,
        'filter'        => 'number_int',
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
        'title'         => _t('Minmum display name'),
        'description'   => _t('Minmum length of display name for user registration'),
        'value'         => 3,
        'filter'        => 'number_int',
        'category'      => 'account',
    ),

    'name_max'     => array(
        'title'         => _t('Maximum display name'),
        'description'   => _t('Maximum length of display name for user registration'),
        'value'         => 32,
        'filter'        => 'number_int',
        'category'      => 'account',
    ),

    'password_min'  => array(
        'title'         => _t('Minimum password'),
        'description'   => _t('Minimum length of password for user registration'),
        'value'         => 5,
        'filter'        => 'number_int',
        'category'      => 'account',
    ),

    'password_max'  => array(
        'title'         => _t('Maximum password'),
        'description'   => _t('Maximum length of password for user registration'),
        'value'         => 32,
        'filter'        => 'number_int',
        'category'      => 'account',
    ),

    'uname_backlist'    => array(
        'title'         => _t('Username backlist'),
        'description'   => _t('Reserved and forbidden username list. Separate each with a <strong>|</strong>, regexp syntax is allowed.'),
        'edit'          => 'textarea',
        'value'         => 'webmaster|^pi|^admin',
        'category'      => 'account',
    ),

    'name_backlist'    => array(
        'title'         => _t('Display backlist'),
        'description'   => _t('Reserved and forbidden username list. Separate each with a <strong>|</strong>, regexp syntax is allowed.'),
        'edit'          => 'textarea',
        'value'         => 'webmaster|^pi|^admin',
        'category'      => 'account',
    ),

    'email_backlist'    => array(
        'title'         => _t('Email backlist'),
        'description'   => _t('Forbidden username list. Separate each with a <strong>|</strong>, regexp syntax is allowed.'),
        'edit'          => 'textarea',
        'value'         => 'pi-engine.org$',
        'category'      => 'account',
    ),

    'rememberme'        => array(
        'title'         => _t('Remember me'),
        'description'   => _t('Days to remember login, 0 for disable.'),
        'value'         => 14,
        'filter'        => 'number_int',
        'category'      => 'account',
    ),

    'attempts'      => array(
        'title'         => _t('Maximum attempts'),
        'description'   => _t('Maximum attempts allowed to try for user login'),
        'value'         => 5,
        'filter'        => 'number_int',
        'category'      => 'account',
    ),

    'login_disable'     => array(
        'title'         => _t('Login disable'),
        'description'   => _t('Disable user login'),
        'edit'          => 'checkbox',
        'value'         => 0,
        'filter'        => 'number_int',
        'category'      => 'account',
    ),

    'register_disable'  => array(
        'title'         => _t('Register disable'),
        'description'   => _t('Disable user registration'),
        'edit'          => 'checkbox',
        'value'         => 0,
        'filter'        => 'number_int',
        'category'      => 'account',
    ),

    'login_captcha'       => array(
        'title'         => _t('Login CAPTCHA'),
        'description'   => _t('Enable CAPTCHA for user login'),
        'edit'          => 'checkbox',
        'value'         => 0,
        'filter'        => 'number_int',
        'category'      => 'account',
    ),

    'register_captcha'  => array(
        'title'         => _t('Register CAPTCHA'),
        'description'   => _t('Enable CAPTCHA for user registration'),
        'edit'          => 'checkbox',
        'value'         => 1,
        'filter'        => 'number_int',
        'category'      => 'account',
    ),
    
    // Avatar
    // Allowed width of avatar image, 0 for no limit
    'max_avatar_width'  => array(
        'title'         => _t('Max Avatar Width'),
        'description'   => _t('Allowed image width, 0 for no limit'),
        'value'         => 2048,
        'category'      => 'avatar',
    ),
    // Allowed height of avatar image, 0 for no limit
    'max_avatar_height' => array(
        'title'         => _t('Max Avatar Height'),
        'description'   => _t('Allowed image height, 0 for no limit'),
        'value'         => 2048,
        'category'      => 'avatar',
    ),
    // Allowed width of avatar image file size, 0 for no limit
    'max_size'          => array(
        'title'         => _t('Max File Size'),
        'description'   => _t('Allowed avatar file to upload (in KB), 0 for no limit'),
        'value'         => 1024,
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