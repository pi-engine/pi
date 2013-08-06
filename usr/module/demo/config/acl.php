<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

return array(
    'resources' => array(
        'front'    => array(
            // test
            'test'  => array(
                'title'         => __('Test resource'),
                'privileges'    => array(
                    'read'  => array(
                        'title'     => __('Read privilege'),
                        'access'    => array(
                            'guest'     => 1,
                            'member'    => 1,
                        )
                    ),
                    'write'  => array(
                        'title'     => __('Write privilege'),
                        'access'    => array(
                            'guest'     => 0,
                            'member'    => 1,
                        )
                    ),
                    'manage'  => array(
                        'title'     => __('Management privilege'),
                        'access'    => array(
                            'guest'     => 0,
                            'moderator' => 1,
                        ),
                    ),
                ),
            ),
        ),
    ),
);
