<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * Route specs
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
);

$config = array(
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
        'category'      => 'general',
    ),

    'uname_min'     => array(
        'title'         => _t('Minimum username'),
        'description'   =>
            _t('Minimum length of username for user registration'),
        'value'         => 3,
        'filter'        => 'int',
        'category'      => 'general',
    ),

    'uname_max'     => array(
        'title'         => _t('Maximum username'),
        'description'   =>
            _t('Maximum length of username for user registration'),
        'value'         => 32,
        'filter'        => 'int',
        'category'      => 'general',
    ),

    'password_min'  => array(
        'title'         => _t('Minimum password'),
        'description'   =>
            _t('Minimum length of password for user registration'),
        'value'         => 5,
        'filter'        => 'int',
        'category'      => 'general',
    ),

    'password_max'  => array(
        'title'         => _t('Maximum password'),
        'description'   => _t('Maximum length of password for user registration'),
        'value'         => 32,
        'filter'        => 'int',
        'category'      => 'general',
    ),

    'uname_backlist'    => array(
        'title'         => _t('Username backlist'),
        'description'   => _t('Reserved and forbidden username list. Separate each with a <strong>|</strong>, regexp syntax is allowed.'),
        'edit'          => 'textarea',
        'value'         => 'webmaster|^pi|^admin|^root',
        'category'      => 'general',
    ),

    'email_backlist'    => array(
        'title'         => _t('Email backlist'),
        'description'   => _t('Forbidden username list. Separate each with a <strong>|</strong>, regexp syntax is allowed.'),
        'edit'          => 'textarea',
        'value'         => 'pi-engine.org$',
        'category'      => 'general',
    ),

    'rememberme'        => array(
        'title'         => _t('Remember me'),
        'description'   => _t('Days to remember login, 0 for disable.'),
        'value'         => 14,
        'filter'        => 'int',
        'category'      => 'general',
    ),

    'attempts'      => array(
        'title'         => _t('Maximum attempts'),
        'description'   => _t('Maximum attempts allowed to try for user login'),
        'value'         => 5,
        'filter'        => 'int',
        'category'      => 'general',
    ),

    'login_captcha'       => array(
        'title'         => _t('Login CAPTCHA'),
        'description'   => _t('Enable CAPTCHA for user login'),
        'edit'          => 'checkbox',
        'value'         => 0,
        'filter'        => 'int',
        'category'      => 'general',
    ),

    'register_captcha'  => array(
        'title'         => _t('Register CAPTCHA'),
        'description'   => _t('Enable CAPTCHA for user registration'),
        'edit'          => 'checkbox',
        'value'         => 1,
        'filter'        => 'int',
        'category'      => 'general',
    ),
    
    // Avatar
    'max_avatar_width'  => array(
        'title'         => _t('Max Avatar Width'),
        'description'   => _t('Avatar width limitation'),
        'value'         => 500,
        'category'      => 'avatar',
    ),
    
    'max_avatar_height' => array(
        'title'         => _t('Max Avatar Height'),
        'description'   => _t('Avatar height limitation'),
        'value'         => 500,
        'category'      => 'avatar',
    ),
    
    'max_size'          => array(
        'title'         => _t('Max File Size'),
        'description'   => _t('Max uploaded file size with unit MB'),
        'value'         => 2,
        'category'      => 'avatar',
    ),
    
    'avatar_extension'  => array(
        'title'         => _t('Format Supported'),
        'description'   => _t('Image extension allowed'),
        'value'         => 'jpg,gif,png,bmp',
        'category'      => 'avatar',
    ),
    
    'path_tmp'          => array(
        'title'         => _t('Temporary Path'),
        'description'   => _t(''),
        'value'         => 'upload/user/tmp',
        'category'      => 'avatar',
    ),
);

return array(
    'category'  => $category,
    'item'      => $config,
);