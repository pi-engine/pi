<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * User register form config custom for eefoucs
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */

return array(
    // Use user module field
    'fullname',
    'telephone',
    'country',
    'province',
    'city',
    'work',
    'interest',
    'email' => array(
        'element' => array(
            'name'  => 'email',
            'type'  => 'hidden',
        )
    ),
    'identity' => array(
        'element' => array(
            'name' => 'identity',
            'type' => 'hidden',
        )
    ),
    'credential' => array(
        'element' => array(
            'name' => 'credential',
            'type' => 'hidden',
        )
    ),
    'name' => array(
        'element' => array(
            'name' => 'name',
            'type' => 'hidden',
        )
    ),
    'registered_source' => array(
        'element' => array(
            'name' => 'registered_source',
            'type' => 'hidden',
        )
    ),
);
