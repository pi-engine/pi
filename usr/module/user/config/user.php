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

        // Account: identity
        'identity'      => array(
            'type'      => 'account',
            'name'      => 'identity',
            'title'     => __('Identity'),
            // Edit element specs
            'edit'      => array(
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
        // Account: password
        'credential'    => array(
            'type'      => 'account',
            'name'      => 'credential',
            'title'     => __('Credential'),
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
        // Account: email
        'email'     => array(
            'type'      => 'account',
            'name'      => 'email',
            'title'     => __('Email'),
            'edit'      => array(
                'element'       => 'email',
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
        // Account: display name
        'name'      => array(
            'type'      => 'account',
            'name'      => 'name',
            'title'     => __('Display name'),
            // Edit element specs
            'edit'      => array(
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
        // Account: gender
        'gender'    => array(
            'type'      => 'account',
            'name'      => 'gender',
            'title'     => __('Gender'),
            'edit'      => 'Module\User\Form\Element\Gender',
            'filter'    => 'Gender',
        ),
        // Account: birth date
        'birthdate'  => array(
            'type'  => 'account',
            'name'  => 'birthdate',
            'title' => __('Birth date'),
            'edit'  => array(
                'element'       => 'date_select',
            ),
        ),
        // Account: avatar
        'avatar'    => array(
            'type'      => 'account',
            'name'      => 'avatar',
            'title'     => __('Avatar'),

            'is_edit'       => false,
            'is_display'    => false,
            'is_search'     => false,
        ),

        // Account: Collective status
        'active'    => array(
            'type'      => 'account',
            'name'      => 'active',
            'title'     => __('Active'),
            'edit'      => 'text',
            'filter'    => 'YesNo',

            'is_edit'       => false,
            'is_display'    => false,
        ),
        // Account: Register time
        'time_created'  => array(
            'type'      => 'account',
            'name'      => 'time_created',
            'title'     => __('Register time'),
            'edit'      => 'text',
            'filter'    => array('Int', 'DateTimeFormatter'),

            'is_edit'       => false,
            'is_display'    => false,
        ),
        // Account: Activation time
        'time_activated'    => array(
            'type'      => 'account',
            'name'      => 'time_activated',
            'title'     => __('Activation time'),
            'edit'      => 'text',
            'filter'    => 'DateTimeFormatter',

            'is_edit'       => false,
            'is_display'    => false,
        ),
        // Account: Disabled time
        'time_disabled' => array(
            'type'      => 'account',
            'name'      => 'time_disabled',
            'title'     => __('Disabled time'),
            'edit'      => 'text',
            'filter'    => 'DateTimeFormatter',

            'is_edit'       => false,
            'is_display'    => false,
            'is_search'     => false,
        ),
        // Account: Deleted time
        'time_deleted'  => array(
            'type'      => 'account',
            'name'      => 'time_deleted',
            'title'     => __('Deleted time'),
            'edit'      => 'text',
            'filter'    => 'DateTimeFormatter',

            'is_edit'       => false,
            'is_display'    => false,
            'is_search'     => false,
        ),

        // Custom profile fields

        // Profile: Full name
        'fullname'  => array(
            'name'      => 'fullname',
            'title'     => __('Full name'),
        ),
        // Profile: location
        'location'  => array(
            'name'  => 'location',
            'title' => __('Location'),
        ),
        // Profile: bio
        'bio'  => array(
            'name'  => 'bio',
            'title' => __('Short bio'),
            'edit'  => 'textarea',
        ),
        // Profile: signature
        'signature'  => array(
            'name'  => 'signature',
            'title' => __('Signature'),
        ),
        // Profile: language
        'language'  => array(
            'name'  => 'language',
            'title' => __('Language'),
            'edit'  => 'locale',
        ),
        // Profile: Register IP
        'ip_register'  => array(
            'name'      => 'ip_register',
            'title'     => __('Register IP'),

            'is_edit'       => false,
            'is_display'    => false,
            'is_search'     => false,
        ),

        // Compound fields
        // Compound: Social networking tools
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

        // Compound: Communication address
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
                'room'      => array(
                    'title' => __('Room'),
                ),
            ),
        ),

        // Compound: Education experiences
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

        // Compound: Profession experiences
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

    // Timeline logs from modules
    'timeline'  => array(
        'operation' => array(
            'title' => __('User action'),
            'icon'  => 'icon-user',
        ),
    ),

    // Activity logs
    'activity'  => array(
    ),

    // Quicklinks
    'quicklink' => array(
        'logout'    => array(
            'title' => __('Logout'),
            'link'  => Pi::user()->getUrl('logout'),
            'icon'  => 'icon-off',
        ),
    ),
);
