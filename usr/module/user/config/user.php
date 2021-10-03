<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

/**
 * User profile and resource specs
 *
 * @see    Pi\Application\Installer\Resource\User
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
return [
    // Fields
    'field'     => [
        // Account: identity
        'identity'          => [
            'type'        => 'account',
            'name'        => 'identity',
            'title'       => _a('Username'),
            // Edit element specs
            'edit'        => [
                'validators' => [
                    [
                        'name' => 'Module\User\Validator\Username',
                    ],
                ],
            ],
            // Is editable by admin, default as true
            'is_edit'     => false,
            'is_required' => true,
        ],

        // Account: password
        'credential'        => [
            'type'        => 'account',
            'name'        => 'credential',
            'title'       => _a('Password'),
            'edit'        => [
                'element'    => 'password',
                'validators' => [
                    [
                        'name' => 'Module\User\Validator\Password',
                    ],
                ],
            ],
            'is_display'  => false,
            'is_search'   => false,
            'is_required' => true,
        ],

        // Account: email
        'email'             => [
            'type'        => 'account',
            'name'        => 'email',
            'title'       => _a('Email'),
            'edit'        => [
                'element'    => 'email',
                'validators' => [
                    [
                        'name'    => 'EmailAddress',
                        'options' => [
                            'useMxCheck'     => false,
                            'useDeepMxCheck' => false,
                            'useDomainCheck' => false,
                        ],
                    ],
                    [
                        'name' => 'Module\User\Validator\UserEmail',
                    ],
                ],
            ],
            'is_edit'     => false,
            'is_required' => true,
        ],

        // Account: display name
        'name'              => [
            'type'        => 'account',
            'name'        => 'name',
            'title'       => _a('Display name'),
            // Edit element specs
            'edit'        => [
                'validators' => [
                    [
                        'name' => 'Module\User\Validator\Name',
                    ],
                ],
            ],
            'is_edit'     => false,
            'is_required' => true,
        ],

        // Account: gender
        'gender'            => [
            'type'   => 'account',
            'name'   => 'gender',
            'title'  => _a('Gender'),
            'edit'   => [
                'element' => 'Module\User\Form\Element\Gender',
            ],
            'filter' => 'Gender',
        ],

        // Account: birth date
        'birthdate'         => [
            'type'   => 'account',
            'name'   => 'birthdate',
            'title'  => _a('Birthdate'),
            'edit'   => [
                'element' => 'datepicker',
                'options' => [
                    'datepicker' => [
                        'format'     => 'yyyy-mm-dd',
                        'start_date' => '1900-01-01',
                        'end_date'   => '2030-12-31',
                        'startView'  => 'decade',
                    ],
                ],
            ],
            'filter' => 'Module\User\Filter\Birthdate',
        ],

        // Account: avatar
        'avatar'            => [
            'type'  => 'account',
            'name'  => 'avatar',
            'title' => _a('Avatar'),

            'is_edit'    => false,
            'is_display' => false,
            'is_search'  => false,
        ],

        // Account: Collective status
        'active'            => [
            'type'   => 'account',
            'name'   => 'active',
            'title'  => _a('Active'),
            'filter' => 'YesNo',

            'is_edit'    => false,
            'is_display' => false,
        ],

        // Account: Register time
        'time_created'      => [
            'type'   => 'account',
            'name'   => 'time_created',
            'title'  => _a('Register time'),
            'filter' => ['Int', 'DateTimeFormatter'],

            'is_edit'    => false,
            'is_display' => false,
        ],

        // Account: Activation time
        'time_activated'    => [
            'type'   => 'account',
            'name'   => 'time_activated',
            'title'  => _a('Activation time'),
            'filter' => ['Int', 'DateTimeFormatter'],

            'is_edit'    => false,
            'is_display' => false,
        ],

        // Account: Disabled time
        'time_disabled'     => [
            'type'   => 'account',
            'name'   => 'time_disabled',
            'title'  => _a('Disabled time'),
            'filter' => ['Int', 'DateTimeFormatter'],

            'is_edit'    => false,
            'is_display' => false,
            'is_search'  => false,
        ],

        // Account: Deleted time
        'time_deleted'      => [
            'type'   => 'account',
            'name'   => 'time_deleted',
            'title'  => _a('Deleted time'),
            'filter' => ['Int', 'DateTimeFormatter'],

            'is_edit'    => false,
            'is_display' => false,
            'is_search'  => false,
        ],

        // Profile fields

        // Profile: Level
        'level'             => [
            'name'       => 'level',
            'title'      => _a('Level'),
            'is_display' => false,
            'is_edit'    => false,
            'is_search'  => true,
        ],

        // Profile: Last modified
        'last_modified'     => [
            'name'       => 'last_modified',
            'title'      => _a('Last modified'),
            'is_display' => false,
            'is_edit'    => false,
            'is_search'  => true,
        ],

        // Profile: homepage
        'homepage'          => [
            'name'  => 'homepage',
            'title' => _a('Personal website'),
            'edit'  => [
                'element' => 'url',
            ],
        ],

        // Profile: bio
        'bio'               => [
            'name'  => 'bio',
            'title' => _a('Short bio'),
            'edit'  => [
                'element' => 'textarea',
            ],
        ],

        // Profile: signature
        'signature'         => [
            'name'  => 'signature',
            'title' => _a('Signature'),
            'edit'  => [
                'element' => 'textarea',
            ],
        ],

        // Profile: Register IP
        'ip_register'       => [
            'name'       => 'ip_register',
            'title'      => _a('Register IP'),
            'is_edit'    => false,
            'is_display' => false,
            'is_search'  => false,
        ],

        // Profile: register source, could be used for register invitation
        'register_source'   => [
            'name'       => 'register_source',
            'title'      => _a('Register source'),
            'is_edit'    => false,
            'is_display' => false,
            'is_search'  => false,
        ],

        // Profile: Two-factor authentication status
        'two_factor_status' => [
            'name'       => 'two_factor_status',
            'title'      => _a('Two factor Status'),
            'is_edit'    => false,
            'is_display' => false,
            'is_search'  => false,
        ],

        // Profile: Two-factor authentication secret
        'two_factor_secret' => [
            'name'       => 'two_factor_secret',
            'title'      => _a('Two factor Secret'),
            'is_edit'    => false,
            'is_display' => false,
            'is_search'  => false,
        ],

        // Profile: identification_number
        // See : https://en.wikipedia.org/wiki/National_identification_number
        'id_number'         => [
            'name'  => 'id_number',
            'title' => _a('Identification number'),
        ],

        // Profile: first_name
        'first_name'        => [
            'name'  => 'first_name',
            'title' => _a('First Name'),
        ],

        // Profile: last_name
        'last_name'         => [
            'name'  => 'last_name',
            'title' => _a('Last Name'),
        ],

        // Profile: age
        'age'               => [
            'name'  => 'age',
            'title' => _a('Age'),
        ],

        // Profile: Language
        'language'          => [
            'name'  => 'language',
            'title' => _a('Language'),
            'edit'  => 'locale',
        ],

        // Profile: mobile number
        'mobile'            => [
            'name'  => 'mobile',
            'title' => _a('Mobile phone'),
        ],

        // Profile: phone number
        'phone'             => [
            'name'  => 'phone',
            'title' => _a('Telephone'),
        ],

        // Profile: address1
        'address1'          => [
            'name'  => 'address1',
            'title' => _a('Address 1'),
        ],

        // Profile: address2
        'address2'          => [
            'name'  => 'address2',
            'title' => _a('Address 2'),
        ],

        // Profile: country
        'country'           => [
            'name'  => 'country',
            'title' => _a('Country'),
            'edit'  => [
                'element' => 'Module\User\Form\Element\Country',
            ],
        ],

        // Profile: state
        'state'             => [
            'name'  => 'state',
            'title' => _a('State'),
        ],

        // Profile: city
        'city'              => [
            'name'  => 'city',
            'title' => _a('City'),
        ],

        // Profile: zip_code
        'zip_code'          => [
            'name'  => 'zip_code',
            'title' => _a('Zip code'),
        ],
        // Profile: device_token
        'device_token'      => [
            'name'  => 'device_token',
            'title' => _a('Device token'),
        ],

        // Compound fields
        // Compound: Social networking tools
        'social'            => [
            'name'    => 'social',
            'title'   => _a('Social tools'),

            // Custom handler
            'handler' => 'Module\User\Field\Social',

            'field' => [
                'title'      => [
                    'title' => _a('Tool name'),
                ],
                'identifier' => [
                    'title' => _a('ID or URL'),
                ],
            ],
        ],
    ],

    // Timeline logs from modules
    'timeline'  => [
        'operation' => [
            'title' => _a('User action'),
            'icon'  => 'fa-user',
        ],
    ],

    // Activity logs
    'activity'  => [
    ],

    // Quicklinks
    'quicklink' => [
        'logout' => [
            'title' => _a('Logout'),
            'link'  => Pi::service('authentication')->getUrl(
                'logout',
                ['section' => 'front']
            ),
            'icon'  => 'fa-power-off',
        ],
    ],
];
