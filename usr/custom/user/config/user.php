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

        // Profile fields

        // Profile: Full name
        'fullname'  => array(
            'name'      => 'fullname',
            'title'     => __('Full name'),
        ),
        // Profile: language
        'language'  => array(
            'name'  => 'language',
            'title' => __('Language'),
            'edit'  => 'locale',
        ),

        // Compound fields

        // Compound: Communication address
        'location'   => array(
            'name'  => 'location',
            'title' => __('Location'),

            'field' => array(
                'country'   => array(
                    'title' => __('Country'),
                ),
                'province'   => array(
                    'title' => __('Province'),
                ),
                'city'   => array(
                    'title' => __('City'),
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
                'description'   => array(
                    'title' => __('Description'),
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

        // Compound: location
        'location'      => array(
            'name'  => 'location',
            'title' => __('Location'),

            'field'     => array(
                'country'    => array(
                    'title' => __('Country'),
                ),
                'province'    => array(
                    'title' => __('Province'),
                ),
                'city'    => array(
                    'title' => __('City'),
                ),
            ),
        ),

        // Compound: Profession interests
        'interest'      => array(
            'name'  => 'interest',
            'title' => __('Interests'),
        ),

        // Compound: Subscriptions
        'subscription'      => array(
            'name'  => 'subscription',
            'title' => __('Subscriptions'),
        ),
    ),

    // Timeline logs from modules
    'timeline'  => array(
    ),

    // Activity logs
    'activity'  => array(
    ),

    // Quicklinks
    'quicklink' => array(
    ),

    // Database schema files
    'database'  => array(
        'sql/mysql.sql',
        'sql/subscription.sql',
        'sql/location.sql'
    ),
);
