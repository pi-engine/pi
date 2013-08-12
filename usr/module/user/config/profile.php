<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * User account and profile specs
 *
 * @see Pi\Application\Installer\Resource\User
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */

$config = Pi::registry('config')->read('user');
return array(
    'account' => array(
        'identity'    => array(
            'title' => __('Identity'),
            // Edit element specs
            'edit'  => array(
                'element'       => 'text',
                'validators'    => array(
                    array(
                        'name'      => 'StringLength',
                        'options'   => array(
                            'encoding'  => 'UTF-8',
                            'min'       => $config['uname_min'],
                            'max'       => $config['uname_max'],
                        ),
                    ),
                    new \Module\User\Validator\UserName(array(
                        'format'            => $config['uname_format'],
                        'backlist'          => $config['uname_backlist'],
                        'checkDuplication'  => true,
                    )),
                ),
            ),
            // Is editable by admin, default as true
            'is_edit'   => false,
        ),
        'credential'    => array(
            'title' => __('Credential'),
            'edit'      => array(
            ),
            'is_edit'   => false,
        ),
        'email'    => array(
            'title' => __('Email'),
            'edit'  => array(
                    'element'   => array(

                    ),
                    'filters'   => array(
                    ),
            ),
        ),
    ),
    'profile' => array(
        'fullname'  => array(
            'title' => __('Full name'),
            'edit'  => 'text',
        ),
        'birthdate'  => array(
            'title' => __('Birth date'),
            'edit'  => array(
                'element'   => array(
                    'type'  => 'Module\User\Form\Element\Birthdate',
                ),
            ),
            'filter'    => array(

            ),
        ),
        'bio'  => array(
            'title' => __('Short bio'),
            'edit'  => 'textarea',
        ),
    ),
);
