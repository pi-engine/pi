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
                'element'       => 'password',
                'validators'    => array(

                ),
            ),
        ),
        'email'    => array(
            'title' => __('Email'),
            'edit'  => array(
                    'validators'    => array(
                    ),
            ),
        ),
    ),
    'profile' => array(
        'fullname'  => array(
            'title' => __('Full name'),
        ),
        'birthdate'  => array(
            'title' => __('Birth date'),
            'edit'  => array(
                'element'       => 'Module\User\Form\Element\Birthdate',
                'filters'       => array(
                ),
                'validators'   => array(
                ),
            ),
        ),
        'bio'  => array(
            'title' => __('Short bio'),
            'edit'  => 'textarea',
        ),
    ),
);
