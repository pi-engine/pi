<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

return array(
    'title'         => __('Pi Engine top contents'),
    'description'   => __('Block to display Pi Engine top and hot contents'),
    'template'      => 'pi-content',
    'render'        => array('PiContent', 'test'),
    'config'        => array(
        // text option
        'subline' => array(
            'title'         => 'Subline',
            'description'   => 'Caption for the block',
            'edit'          => 'text',
            'filter'        => 'string',
            'value'         => __('Enjoy creating and sharing'),
        ),
        // Yes or No option
        'show_github'    => array(
            'title'         => 'Github activities',
            'description'   => 'To display commit activites from github.',
            'edit'          => 'checkbox',
            //'filter'        => 'number_int',
            'value'         => '1',
        ),
    ),
    'access'        => array(
        'guest'     => 0,
        'member'    => 1,
    ),
);
