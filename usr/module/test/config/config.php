<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

return array(
    // Categories for config edit or display
    'category'  => array(
        array(
            'title' => _t('General'),
            'name'  => 'general',
        ),
        array(
            'title' => _t('Test'),
            'name'  => 'test'
        ),
    ),
    // Config items
    'item'         => array(
        'item_per_page' => array(
            'category'      => 'general',
            'title'         => _t('Item per page'),
            'description'   => _t('Number of items on one page.'),
            'value'         => 2,
            'filter'        => 'int',
            'edit'          => array(
                'type'      => 'select',
                'options'   => array(
                    'options'   => array(
                        2   => '2',
                        10  => '10',
                        20  => '20',
                        50  => '50',
                    ),
                ),
            ),
        ),
        'login_disable'     => array(
            'title'         => _t('Login disable'),
            'description'   => _t('用户名启用'),
            'edit'          => 'checkbox',
            'value'         => 0,
            'filter'        => 'int',
            'category'      => 'general',
        ),
        'phone_disable'  => array(
            'title'         => _t('Phone disable'),
            'description'   => _t('电话启用'),
            'edit'          => 'checkbox',
            'value'         => 0,
            'filter'        => 'int',
            'category'      => 'general',
        ),
        'email_disable'  => array(
            'title'         => _t('Email disable'),
            'description'   => _t('邮箱启用'),
            'edit'          => 'checkbox',
            'value'         => 0,
            'filter'        => 'int',
            'category'      => 'general',
        ),
        'message_disable'  => array(
            'title'         => _t('Message disable'),
            'description'   => _t('留言启用'),
            'edit'          => 'checkbox',
            'value'         => 0,
            'filter'        => 'int',
            'category'      => 'general',
        ),

        'test'  => array(
            'category'      => 'test',
            'title'         => _t('Test Config'),
            'description'   => _t('An example for configuration.'),
            'value'         => 'Configuration text for testing'
        ),
    )
);
