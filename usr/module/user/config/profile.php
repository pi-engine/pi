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

    // Account profile fields
    'account' => array(
        'identity'    => array(
            'name'  => 'identity',
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
                    array(
                        'name'      => 'Module\User\Validator\Username',
                        'options'   => array(
                            'format'            => $config['uname_format'],
                            'backlist'          => $config['uname_backlist'],
                            'checkDuplication'  => true,
                        ),
                    ),
                ),
            ),
            // Is editable by admin, default as true
            'is_edit'   => false,
        ),
        'credential'    => array(
            'name'  => 'credential',
            'title' => __('Credential'),
            'edit'      => array(
                'element'       => 'password',
                'validators'    => array(
                    array(
                        'name'      => 'StringLength',
                        'options'   => array(
                            'encoding'  => 'UTF-8',
                            'min'       => $config['password_min'],
                            'max'       => $config['password_max'],
                        ),
                    ),
                ),
            ),
        ),
        'email'    => array(
            'name'  => 'email',
            'title' => __('Email'),
            'edit'  => array(
                'validators'    => array(
                    array(
                        'name'      => 'EmailAddress',
                        'options'   => array(
                            'useMxCheck'        => false,
                            'useDeepMxCheck'    => false,
                            'useDomainCheck'    => false,
                        ),
                    ),
                    array(
                        'name'      => 'Module\User\Validator\UserEmail',
                        'options'   => array(
                            'backlist'          => $config['email_backlist'],
                            'checkDuplication'  => true,
                        ),
                    ),
                ),
            ),
        ),
        'name'    => array(
            'name'  => 'name',
            'title' => __('Display name'),
            // Edit element specs
            'edit'  => array(
                'validators'    => array(
                    array(
                        'name'      => 'StringLength',
                        'options'   => array(
                            'encoding'  => 'UTF-8',
                            'min'       => $config['name_min'],
                            'max'       => $config['name_max'],
                        ),
                    ),
                    array(
                        'name'      => 'Module\User\Validator\Name',
                        'options'   => array(
                            'backlist'          => $config['name_backlist'],
                            'checkDuplication'  => true,
                        ),
                    ),
                ),
            ),
        ),
        'active'    => array(
            'name'      => 'active',
            'title'     => __('Activated'),
            'is_edit'   => false,
        ),
        'disabled'  => array(
            'name'      => 'disabled',
            'title'     => __('Disabled'),
            'edit'      => 'checkbox',
            'is_edit'   => false,
        ),
    ),

    // Basic profile fields
    'profile' => array(
        'fullname'  => array(
            'name'  => 'fullname',
            'title' => __('Full name'),
        ),

        'birthdate'  => array(
            'name'  => 'birthdate',
            'title' => __('Birth date'),
            'edit'  => array(
                'element'       => 'Module\User\Form\Element\Birthdate',
                'filters'       => array(
                    array(
                        'name'  => 'Module\User\Filter\Birthdate',
                    ),
                ),
                'validators'   => array(
                    array(
                        'name'  => 'Module\User\Validator\Birthdate',
                    ),
                ),
            ),
        ),

        'location'  => array(
            'name'  => 'location',
            'title' => __('Location'),
        ),

        'signation'  => array(
            'name'  => 'signature',
            'title' => __('Signature'),
        ),

        'bio'  => array(
            'name'  => 'bio',
            'title' => __('Short bio'),
            'edit'  => 'textarea',
        ),
    ),

);
