<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

return array(

    'field' => array(
        'sample'    => array(
            'title' => _a('Sample field'),
        ),
    ),

    // Activity
    'activity' => array(
        'test-callback'    => array(
            'title' => _a('Test callback'),
            'icon'  => 'icon-post',
            'callback'  => 'Module\Demo\ActivityTest',
        ),
        'test-template'   => array(
            'title' => _a('Test template'),
            'icon'  => 'icon-post',
            'template' => 'demo-activity-test',
            'callback' => Pi::url(Pi::service('url')->assemble('default', array('module' => 'demo', 'controller' => 'activity', 'action' => 'get')), true),
        ),
    ),
);
