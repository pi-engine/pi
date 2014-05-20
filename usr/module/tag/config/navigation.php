<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

return array(
    'front'     => false,
    'admin'     => array(
        'top'   => array(
            'label'         => _t('Top tags'),
            'route'         => 'admin',
            'controller'    => 'index',
            'action'        => 'top',
        ),
        'new'   => array(
            'label'         => _t('New tags'),
            'route'         => 'admin',
            'controller'    => 'index',
            'action'        => 'new',
        ),
        'items'    => array(
            'label'         => _t('Tagged items'),
            'route'         => 'admin',
            'controller'    => 'index',
            'action'        => 'link',
        ),
    ),
);
