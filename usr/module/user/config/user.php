<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * User profile and resource specs
 *
 * @see Pi\Application\Installer\Resource\User
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return array(
    // Fields
    'field'     => array(

        // Account fields
        'identity'    => array(
            'type'  => 'account',
            'name'  => 'identity',
            'title' => __('Identity'),
            // Edit element specs
            'edit'  => array(
                'validators'    => array(
                    array(
                        'name'      => 'StringLength',
                    ),
                    array(
                        'name'      => 'Module\User\Validator\Username',
                        /*
                        'options'   => array(
                            'encoding'          => 'UTF-8',
                            'min'               => $config['uname_min'],
                            'max'               => $config['uname_max'],
                            'format'            => $config['uname_format'],
                            'backlist'          => $config['uname_backlist'],
                            'checkDuplication'  => true,
                        ),
                        */
                    ),
                ),
            ),
            // Is editable by admin, default as true
            'is_edit'   => false,
        ),
        'credential'    => array(
            'type'  => 'account',
            'name'  => 'credential',
            'title' => __('Credential'),
            'edit'      => array(
                'element'       => 'password',
                'validators'    => array(
                    array(
                        'name'      => 'Module\User\Validator\Password',
                        /*
                        'options'   => array(
                            'encoding'  => 'UTF-8',
                            'min'       => $config['password_min'],
                            'max'       => $config['password_max'],
                        ),
                        */
                    ),
                ),
            ),
        ),
        'email'    => array(
            'type'  => 'account',
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
                        /*
                        'options'   => array(
                            'backlist'          => $config['email_backlist'],
                            'checkDuplication'  => true,
                        ),
                        */
                    ),
                ),
            ),
        ),
        'name'    => array(
            'type'  => 'account',
            'name'  => 'name',
            'title' => __('Display name'),
            // Edit element specs
            'edit'  => array(
                'validators'    => array(
                    array(
                        'name'      => 'Module\User\Validator\Name',
                        /*
                        'options'   => array(
                            'encoding'          => 'UTF-8',
                            'min'               => $config['name_min'],
                            'max'               => $config['name_max'],
                            'backlist'          => $config['name_backlist'],
                            'checkDuplication'  => true,
                        ),
                        */
                    ),
                ),
            ),
        ),

        // Profile fields
        'fullname'  => array(
            'type'  => 'profile',
            'name'  => 'fullname',
            'title' => __('Full name'),
        ),

        'birthdate'  => array(
            'type'  => 'profile',
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
            'type'  => 'profile',
            'name'  => 'location',
            'title' => __('Location'),
        ),

        'signature'  => array(
            'type'  => 'profile',
            'name'  => 'signature',
            'title' => __('Signature'),
        ),

        'bio'  => array(
            'type'  => 'profile',
            'name'  => 'bio',
            'title' => __('Short bio'),
            'edit'  => 'textarea',
        ),

        // Custom fields
        'language'  => array(
            'title' => __('Language'),
            'edit'  => 'Locale',
        ),

        // Compound fields
        // Social networking tools
        'tool'      => array(
            'name'  => 'tool',
            'title' => __('Social tools'),

            'field' => array(
                'title'         => array(
                    'title' => __('Tool name'),
                ),
                'identifier'    => array(
                    'title' => __('ID or URL'),
                ),
            ),
        ),

        // Communication address
        'address'   => array(
            'name'  => 'address',
            'title' => __('Address'),

            'field' => array(
                'postcode'   => array(
                    'title' => __('Post code'),
                ),
                'country'   => array(
                    'title' => __('Country'),
                ),
                'province'   => array(
                    'title' => __('Province'),
                ),
                'city'   => array(
                    'title' => __('City'),
                ),
                'street'   => array(
                    'title' => __('Street'),
                ),
                'office'   => array(
                    'title' => __('Office'),
                ),
            ),
        ),

        // Education experiences
        'education'  => array(
            'name'  => 'education',
            'title' => __('Education'),

            'field' => array(
                'school'    => array(
                    'title' => __('School name'),
                ),
                'major'    => array(
                    'title' => __('Major'),
                ),
                'degree'    => array(
                    'title' => __('Degree'),
                ),
                'class'    => array(
                    'title' => __('Class'),
                ),
                'start'    => array(
                    'title' => __('Start time'),
                ),
                'end'    => array(
                    'title' => __('End time'),
                ),
            ),
        ),

        // Profession experiences
        'work'      => array(
            'name'  => 'work',
            'title' => __('Work'),

            'field' => array(
                'company'    => array(
                    'title' => __('Company name'),
                ),
                'department'    => array(
                    'title' => __('Department'),
                ),
                'title'    => array(
                    'title' => __('Job title'),
                ),
                'description'   => array(
                    'title' => __('Description'),
                    'edit'  => 'textarea',
                ),
                'start'    => array(
                    'title' => __('Start time'),
                ),
                'end'    => array(
                    'title' => __('End time'),
                ),
            ),
        ),

    ),

    'timeline'  => array(
        'action'    => array(
            'title' => __('User action'),
        ),
    ),
    'activity'  => array(
        'member'    => array(

        ),
    ),
    'quicklink' => array(

    ),
);
