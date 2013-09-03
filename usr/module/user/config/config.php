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
    'is_original'       => array(
        'title'         => _t('Original'),
        'description'   => _t('Enable original photo'),
        'edit'          => 'checkbox',
        'value'         => 1,
        'filter'        => 'number_int',
        'category'      => 'avatar',
    ),
    
    'is_xxlarge'        => array(
        'title'         => _t('XXLarge'),
        'description'   => _t('Enable xxlarge photo'),
        'edit'          => 'checkbox',
        'value'         => 0,
        'filter'        => 'number_int',
        'category'      => 'avatar',
    ),
    
    'is_xlarge'         => array(
        'title'         => _t('XLarge'),
        'description'   => _t('Enable xlarge photo'),
        'edit'          => 'checkbox',
        'value'         => 1,
        'filter'        => 'number_int',
        'category'      => 'avatar',
    ),
    
    'is_large'          => array(
        'title'         => _t('Large'),
        'description'   => _t('Enable large photo'),
        'edit'          => 'checkbox',
        'value'         => 0,
        'filter'        => 'number_int',
        'category'      => 'avatar',
    ),
    
    'is_normal'         => array(
        'title'         => _t('Normal'),
        'description'   => _t('Enable normal photo'),
        'edit'          => 'checkbox',
        'value'         => 0,
        'filter'        => 'number_int',
        'category'      => 'avatar',
    ),
    
    'is_small'          => array(
        'title'         => _t('Small'),
        'description'   => _t('Enable small photo'),
        'edit'          => 'checkbox',
        'value'         => 1,
        'filter'        => 'number_int',
        'category'      => 'avatar',
    ),
    
    'is_xsmall'         => array(
        'title'         => _t('XSmall'),
        'description'   => _t('Enable xsmall photo'),
        'edit'          => 'checkbox',
        'value'         => 1,
        'filter'        => 'number_int',
        'category'      => 'avatar',
    ),
    
    'is_xxsmall'        => array(
        'title'         => _t('XXSmall'),
        'description'   => _t('Enable xxsmall photo'),
        'edit'          => 'checkbox',
        'value'         => 1,
        'filter'        => 'number_int',
        'category'      => 'avatar',
    ),
    
    'is_mini'           => array(
        'title'         => _t('Mini'),
        'description'   => _t('Enable mini photo'),
        'edit'          => 'checkbox',
        'value'         => 0,
        'filter'        => 'number_int',
        'category'      => 'avatar',
    ),
    
    'xxlarge_size'      => array(
        'title'         => _t('XXLarge Size'),
        'description'   => _t('Default xxlarge size'),
        'value'         => 214,
        'filter'        => 'number_int',
        'category'      => 'avatar',
    ),
    
    'xlarge_size'       => array(
        'title'         => _t('XLarge Size'),
        'description'   => _t('Default xlarge size'),
        'value'         => 120,
        'filter'        => 'number_int',
        'category'      => 'avatar',
    ),
    
    'large_size'        => array(
        'title'         => _t('Large Size'),
        'description'   => _t('Default large size'),
        'value'         => 96,
        'filter'        => 'number_int',
        'category'      => 'avatar',
    ),
    
    'normal_size'       => array(
        'title'         => _t('Normal Size'),
        'description'   => _t('Default normal size'),
        'value'         => 80,
        'filter'        => 'number_int',
        'category'      => 'avatar',
    ),
    
    'small_size'        => array(
        'title'         => _t('Small Size'),
        'description'   => _t('Default small size'),
        'value'         => 46,
        'filter'        => 'number_int',
        'category'      => 'avatar',
    ),
    
    'xsmall_size'       => array(
        'title'         => _t('XSmall Size'),
        'description'   => _t('Default xsmall size'),
        'value'         => 28,
        'filter'        => 'number_int',
        'category'      => 'avatar',
    ),
    
    'xxsmall_size'      => array(
        'title'         => _t('XXSmall Size'),
        'description'   => _t('Default xxsmall size'),
        'value'         => 24,
        'filter'        => 'number_int',
        'category'      => 'avatar',
    ),
    
    'mini_size'         => array(
        'title'         => _t('Mini Size'),
        'description'   => _t('Default mini size'),
        'value'         => 16,
        'filter'        => 'number_int',
        'category'      => 'avatar',
    ),
    
    'source_repository' => array(
        'title'         => _t('Select From Repository'),
        'description'   => _t(''),
        'value'         => 1,
        'edit'          => 'checkbox',
        'filter'        => 'number_int',
        'category'      => 'avatar',
    ),
    
    'source_gravatar'   => array(
        'title'         => _t('From Gravatar'),
        'description'   => _t(''),
        'value'         => 1,
        'edit'          => 'checkbox',
        'filter'        => 'number_int',
        'category'      => 'avatar',
    ),
    
    'source_upload'     => array(
        'title'         => _t('Upload From User'),
        'description'   => _t(''),
        'value'         => 1,
        'edit'          => 'checkbox',
        'filter'        => 'number_int',
        'category'      => 'avatar',
    ),
    
    'default_source'    => array(
        'title'         => _t('Default Source'),
        'description'   => _t('Default source to get avatar'),
        'value'         => 'gravatar',
        'edit'          => array(
            'type'          => 'select',
            'options'       => array(
                'options'       => array(
                    'repository'    => __('From repository'),
                    'gravatar'      => __('From gravatar'),
                    'upload'        => __('From upload'),
                )
            )
        ),
        'filter'        => 'string',
        'category'      => 'avatar',
    ),
    
    'avatar_extension'  => array(
        'title'         => _t('Format Supported'),
        'description'   => _t('Seprate by comma'),
        'value'         => 'jpg,png,gif',
        'category'      => 'avatar',
    ),
    
    'max_size'          => array(
        'title'         => _t('File Size'),
        'description'   => _t('Max uploaded file size with unit MB'),
        'value'         => '2',
        'category'      => 'avatar',
    ),
    
    'path_avatar'       => array(
        'title'         => _t('Upload Directory'),
        'description'   => _t('Path to storing avatar'),
        'value'         => 'upload/user/avatar',
        'category'      => 'avatar',
    ),
    
    'sub_dir_pattern'   => array(
        'title'         => _t('Pattern'),
        'description'   => _t('Use datetime as pattern of sub directory'),
        'value'         => _t('Y/m/d'),
        'edit'          => array(
            'type'      => 'select',
            'options'   => array(
                'options'   => array(
                    'Y/m/d'     => 'Y/m/d',
                    'Y/m'       => 'Y/m',
                    'Ym'        => 'Ym',
                ),
            ),
        ),
        'category'      => 'avatar',
    ),
);

return array(
    'category'  => $category,
    'item'      => $config,
);