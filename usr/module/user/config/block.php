<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

/**
 * User module meta
 *
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
return array(
    'completeness' => array(
        'title' => _a('Profile completeness'),
        'description' => '',
        'render' => array('block', 'completeness'),
        'template' => 'completeness',
        'config' => array(
            'max_percent' => array(
                'title' => _a('Max percent to active block visibility'),
                'description' => '',
                'edit' => 'text',
                'filter' => 'number_int',
                'value' => 99,
            ),
            'hide_main_fields' => array(
                'title' => _a('Hide after complete main fields'),
                'description' => '',
                'edit' => 'checkbox',
                'filter' => 'number_int',
                'value' => 0,
            ),
        ),
    ),
);