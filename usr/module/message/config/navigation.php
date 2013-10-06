<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

return array(
    'front'      => array(
        'private' => array(
            'label'         => _t('Private message'),
            'route'         => 'default',
            'controller'    => 'index',
            'action'        => 'index',
        ),
        'notify' => array(
            'label'         => _t('Notification'),
            'route'         => 'default',
            'controller'    => 'notify',
            'action'        => 'index',
        ),
        'send' => array(
            'label'         => _t('New message'),
            'route'         => 'default',
            'controller'    => 'index',
            'action'        => 'send',
        ),
    ),
);
