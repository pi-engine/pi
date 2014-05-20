<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt BSD 3-Clause License
 */
return array(
    'search'    => array(
        'name'          => 'search',
        'title'         => _b('Search'),
        'description'   => _b('Search box.'),
        'render'        => array('block', 'search'),
        'template'      => 'search',
        'config'        => array(
            'target' => array(
                'title'         => _a('Target'),
                'edit'          => array(
                    'type'          => 'select',
                    'options'    => array(
                        'options'   => array(
                            '_self'    => _a('_self'),
                            '_blank'   => _a('_blank'),
                        ),
                    ),
                ),
                'value'         => '_self', 
            )
        )
    ),
);