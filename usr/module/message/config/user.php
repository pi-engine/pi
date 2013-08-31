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

        // Profile: New messages since last visit to alert
        'message_alert' => array(
            'name'      => 'message_alert',
            'title'     => __('Message to alert'),
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
        'member'    => array(
        ),
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
