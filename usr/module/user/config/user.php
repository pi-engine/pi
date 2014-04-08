<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
            'title'     => _a('Username'),
            // Edit element specs
            'edit'      => array(
                'validators'    => array(
                    array(
                        'name'      => 'Module\User\Validator\Username',
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
            'title'     => _a('Password'),
            'edit'      => array(
                'element'       => 'password',
                'validators'    => array(
                    array(
                        'name'      => 'Module\User\Validator\Password',
                    ),
                ),
            ),
            'is_display'    => false,
            'is_search'     => false,
        ),
        // Account: email
        'email'     => array(
            'type'      => 'account',
            'name'      => 'email',
            'title'     => _a('Email'),
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
                    ),
                ),
            ),
            'is_edit'   => false,
        ),
        // Account: display name
        'name'      => array(
            'type'      => 'account',
            'name'      => 'name',
            'title'     => _a('Display name'),
            // Edit element specs
            'edit'      => array(
                'validators'    => array(
                    array(
                        'name'      => 'Module\User\Validator\Name',
                    ),
                ),
            ),
            'is_edit'   => false,
        ),
        // Account: gender
        'gender'    => array(
            'type'      => 'account',
            'name'      => 'gender',
            'title'     => _a('Gender'),
            'edit'      => array(
                'element'   =>  'Module\User\Form\Element\Gender',
            ),
            'filter'    => 'Gender',
        ),
        // Account: birth date
        'birthdate'  => array(
            'type'  => 'account',
            'name'  => 'birthdate',
            'title' => _a('Birthdate'),
            'edit'  => array(
                'element'       => 'date_select',
                'options'       => array(
                    'create_empty_option'   => true,
                    'year_attributes' => array(
                        'class' => 'input-small',
                    ),
                    'month_attributes' => array(
                        'class' => 'input-small',
                    ),
                    'day_attributes' => array(
                        'class' => 'input-small',
                    ),
                ),
                'filters'   => array(array(
                    'name'  => 'Pi\Filter\DateSelect',
                )),
                'validators'   => array(array(
                    'name'  => 'Pi\Validator\DateSelect',
                )),
            ),
            'filter'   => 'Module\User\Filter\Birthdate',
        ),
        // Account: avatar
        'avatar'    => array(
            'type'      => 'account',
            'name'      => 'avatar',
            'title'     => _a('Avatar'),

            'is_edit'       => false,
            'is_display'    => false,
            'is_search'     => false,
        ),

        // Account: Collective status
        'active'    => array(
            'type'      => 'account',
            'name'      => 'active',
            'title'     => _a('Active'),
            'filter'    => 'YesNo',

            'is_edit'       => false,
            'is_display'    => false,
        ),
        // Account: Register time
        'time_created'  => array(
            'type'      => 'account',
            'name'      => 'time_created',
            'title'     => _a('Register time'),
            'filter'    => array('Int', 'DateTimeFormatter'),

            'is_edit'       => false,
            'is_display'    => false,
        ),
        // Account: Activation time
        'time_activated'    => array(
            'type'      => 'account',
            'name'      => 'time_activated',
            'title'     => _a('Activation time'),
            'filter'    => 'DateTimeFormatter',

            'is_edit'       => false,
            'is_display'    => false,
        ),
        // Account: Disabled time
        'time_disabled' => array(
            'type'      => 'account',
            'name'      => 'time_disabled',
            'title'     => _a('Disabled time'),
            'filter'    => 'DateTimeFormatter',

            'is_edit'       => false,
            'is_display'    => false,
            'is_search'     => false,
        ),
        // Account: Deleted time
        'time_deleted'  => array(
            'type'      => 'account',
            'name'      => 'time_deleted',
            'title'     => _a('Deleted time'),
            'filter'    => 'DateTimeFormatter',

            'is_edit'       => false,
            'is_display'    => false,
            'is_search'     => false,
        ),

        // Profile fields

        // Profile: Level
        'level'    => array(
            'name'      => 'level',
            'title'     => _a('Level'),

            'is_display'    => false,
            'is_edit'       => false,
            'is_search'     => true,
        ),
        // Profile: Last modified
        'last_modified'  => array(
            'name'  => 'last_modified',
            'title' => _a('Last modified'),
            'is_display'    => false,
            'is_edit'       => false,
            'is_search'     => true,
        ),
        // Profile: homepage
        'homepage'  => array(
            'name'  => 'homepage',
            'title' => _a('Personal website'),
            'edit'  => array(
                'element'       => 'url',
            ),
        ),
        // Profile: bio
        'bio'  => array(
            'name'  => 'bio',
            'title' => _a('Short bio'),
            'edit'  => array(
                'element'       => 'textarea',
            ),
        ),
        // Profile: signature
        'signature'  => array(
            'name'  => 'signature',
            'title' => _a('Signature'),
            'edit'  => array(
                'element'   => 'textarea',
            ),
        ),

        // Profile: Register IP
        'ip_register'  => array(
            'name'      => 'ip_register',
            'title'     => _a('Register IP'),

            'is_edit'       => false,
            'is_display'    => false,
            'is_search'     => false,
        ),

        // Compound fields
        // Compound: Social networking tools
        'social'      => array(
            'name'  => 'social',
            'title' => _a('Social tools'),

            // Custom handler
            'handler'   => 'Module\User\Field\Social',

            'field' => array(
                'title'         => array(
                    'title' => _a('Tool name'),
                ),
                'identifier'    => array(
                    'title' => _a('ID or URL'),
                ),
            ),
        ),

    ),

    // Timeline logs from modules
    'timeline'  => array(
        'operation' => array(
            'title' => _a('User action'),
            'icon'  => 'icon-user',
        ),
    ),

    // Activity logs
    'activity'  => array(
    ),

    // Quicklinks
    'quicklink' => array(
        'logout'    => array(
            'title' => _a('Logout'),
            'link'  => Pi::service('authentication')->getUrl('logout'),
            'icon'  => 'icon-off',
        ),
    ),
);
