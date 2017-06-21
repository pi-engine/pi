<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
return array(
    // route name
    'partenaires' => array(
        'name' => 'portfolio',
        'type' => 'Custom\Portfolio\Route\Portfolio',
        'options' => array(
            'route' => '/partenaires',
            'defaults' => array(
                'module' => 'partenaires',
                'controller' => 'index',
                'action' => 'index'
            )
        ),
    )
);