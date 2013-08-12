<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * User custom profile and resource specs
 *
 * @see Pi\Application\Installer\Resource\User
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return array(
    'account' => array(
        'identity'    => array(
            // Edit element specs
            'edit'      => array(
            ),
            // Is editable by admin, default as true
            'is_edit'   => false,
        ),
        'credential'    => array(
            'edit'      => array(
            ),
            'is_edit'   => false,
        ),
        'email'    => array(
            'edit'          => array(
                    'element'   => array(

                    ),
                    'filter'   => array(
                        ''
                    )
            ),
        ),
    ),
    'profile' => array(
        'fullname'  => array(
            'edit'          => 'text',
        ),
        'bio'  => array(
            'edit'          => 'textarea',
        ),
    ),
);
