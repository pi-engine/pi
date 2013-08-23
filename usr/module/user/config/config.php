<?php
/**
 * User module configs
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) http://www.eefocus.com
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Liu Chuang <liuchuag@eefocus.com>
 * @since           1.0
 * @package         Module\User
 */

$config = array();

// Config categories
$config['category'] = array(
    array(
        'name'     => 'general',
        'title'    => _t('General'),
    ),
);

// Config items

$config['item'] = array(
    // General section

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
        'title'         => _t('Minmum username'),
        'description'   => _t('Minmum length of username for user registration'),
        'value'         => 3,
        'filter'        => 'number_int',
        'category'      => 'general',
    ),

    'uname_max'     => array(
        'title'         => _t('Maximum username'),
        'description'   => _t('Maximum length of username for user registration'),
        'value'         => 32,
        'filter'        => 'number_int',
        'category'      => 'general',
    ),

    'password_min'  => array(
        'title'         => _t('Minmum password'),
        'description'   => _t('Minmum length of password for user registration'),
        'value'         => 5,
        'filter'        => 'number_int',
        'category'      => 'general',
    ),

    'password_max'  => array(
        'title'         => _t('Maximum password'),
        'description'   => _t('Maximum length of password for user registration'),
        'value'         => 32,
        'filter'        => 'number_int',
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
        'filter'        => 'number_int',
        'category'      => 'general',
    ),

    'attempts'      => array(
        'title'         => _t('Maximum attempts'),
        'description'   => _t('Maximum attempts allowed to try for user login'),
        'value'         => 5,
        'filter'        => 'number_int',
        'category'      => 'general',
    ),

    'login_captcha'       => array(
        'title'         => _t('Login CAPTCHA'),
        'description'   => _t('Enable CAPTCHA for user login'),
        'edit'          => 'checkbox',
        'value'         => 0,
        'filter'        => 'number_int',
        'category'      => 'general',
    ),

    'register_captcha'  => array(
        'title'         => _t('Register CAPTCHA'),
        'description'   => _t('Enable CAPTCHA for user registration'),
        'edit'          => 'checkbox',
        'value'         => 1,
        'filter'        => 'number_int',
        'category'      => 'general',
    ),
);

return $config;