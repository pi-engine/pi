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

        // Profile: Telephone
        'telephone'  => array(
            'name'      => 'telephone',
            'title'     => __('Telephone'),
            'edit'      => array(
                'element' => array(
                    'type'          => 'textarea',
                    'attributes'    => array(
                        'class' => 'large',
                    ),
                ),
            ),
        ),

        // Profile: Address
        'address'  => array(
            'name'      => 'address',
            'title'     => __('Address'),
        ),

        // Profile: Zip code
        'zip_code'  => array(
            'name'      => 'zip_code',
            'title'     => __('Zip code'),
        ),

        // Profile: Language
        'language'  => array(
            'name'  => 'language',
            'title' => __('Language'),
            'edit'  => 'locale',
        ),

        // Profile: Country
        'country'  => array(
            'name'  => 'country',
            'title' => __('Country'),
            'edit'  => 'Custom\User\Form\Element\Location',
        ),

        // Profile: Province
        'province'  => array(
            'name'  => 'province',
            'title' => __('Province'),

            'edit'  => 'hidden',
        ),

        // Profile: City
        'city'  => array(
            'name'  => 'city',
            'title' => __('city'),

            'edit'  => 'hidden',
        ),

        // Compound fields

        // Compound: Education experiences
        'education'  => array(
            'name'  => 'education',
            'title' => __('Education'),

            // Custom handler
            'handler'   => 'Custom\User\Compound\Education',

            // Fields
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

            // Custom handler
            'handler'   => 'Custom\User\Compound\Work',

            // Fields
            'field' => array(
                'company'    => array(
                    'title' => __('Company name'),
                ),
                'department'    => array(
                    'title' => __('Department'),
                ),
                'industry'    => array(
                    'title' => __('Industry'),
                ),
                'sector'    => array(
                    'title' => __('Sector'),
                ),
                'position'    => array(
                    'title' => __('Job Position'),
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

        // Compound: Profession interests
        'interest'      => array(
            'name'  => 'interest',
            'title' => __('Interests'),

            // Custom handler
            'handler'   => 'Custom\User\Compound\Interest',

            // Fields
            'field' => array(
                'interest' => array(
                    'title' => __('Interest'),
                ),
            ),
        ),

        // Compound: Subscriptions
        'subscription'      => array(
            'name'  => 'subscription',
            'title' => __('Subscriptions'),

            // Custom handler
            'handler'   => 'Custom\User\Compound\Subscription',

            // Fields
            'field' => array(
                'item' => array(
                    'title' => __('Item'),
                ),
            ),
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

);
