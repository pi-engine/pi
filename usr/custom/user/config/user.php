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
//return array();
return array(
    // Fields
    'field'     => array(

        // Profile fields

        // Profile: Full name
        'fullname'  => array(
            'name'      => 'fullname',
            'title'     => __('Full name'),
            'edit' => array(
                'required' => true,
            ),
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
            'edit' => array(
                'required' => true,
                'element'  => 'Custom\User\Form\Element\Location',
            ),
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
            'title' => __('City'),

            'edit'  => 'hidden',
        ),

        //Contact
        'telephone' => array(
            'name'  => 'telephone',
            'title' => __('Telephone'),
            'edit' => array(
                'required' => true,
            ),
        ),

        // Profile: Province
        'registered_source'  => array(
            'name'  => 'registered_source',
            'title' => __('Registered source'),
            'is_display'    => false,
            'is_search'     => false,
        ),

        'address' => array(
            'name'  => 'address',
            'title' => __('Address'),
            'edit'  => array(
                'required' => true,
                'class' => 'input-xxlarge',
                'attributes' => array(
                    'class' => 'input-xxlarge'
                )

            )
        ),

        'postcode' => array(
            'name'  => 'postcode',
            'title' => __('Postcode'),
            'edit' => array(
                'required' => true,
            ),
        ),

        'interest' => array(
            'name'  => 'interest',
            'title' => __('Interest'),
            'edit'  => 'Custom\User\Form\Element\Checkbox',
            // Custom handler
            'handler'   => 'Custom\User\Field\Interest',
        ),

        'subscription'      => array(
            'name'  => 'subscription',
            'title' => __('Subscriptions'),
            'edit'  => 'Custom\User\Form\Element\Checkbox',
            // Custom handler
            'handler'   => 'Custom\User\Field\Subscription',
        ),

        // Compound fields

        // Compound: Education experiences
        'education'  => array(
            'name'  => 'education',
            'title' => __('Education'),

            // Custom handler
            'handler'   => 'Custom\User\Field\Education',

            // Fields
            'field' => array(
                'school'    => array(
                    'title' => __('School name'),
                    'edit' => array(
                        'required' => true,
                    ),
                ),
                'department'    => array(
                    'title' => __('Department'),
                    'edit' => array(
                        'required' => true,
                    ),
                ),
                'major'    => array(
                    'title' => __('Major'),
                    'edit' => array(
                        'required' => true,
                    ),
                ),
                'degree'    => array(
                    'title' => __('Degree'),
                    'edit' => array(
                        'required' => true,
                    ),
                ),
                'start'    => array(
                    'title' => __('Start time'),
                    'edit'  => 'Custom\User\Form\Element\StartTime',
                ),
                'end'    => array(
                    'title' => __('End time'),
                    'edit'  => 'Custom\User\Form\Element\EndTime',
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
            'handler'   => 'Custom\User\Field\Work',

            // Fields
            'field' => array(
                'company'    => array(
                    'title' => __('Company name'),
                    'edit' => array(
                        'required' => true,
                    ),
                ),
                'department'    => array(
                    'title' => __('Department'),
                    'edit' => array(
                        'required' => true,
                    ),
                ),
                'industry'    => array(
                    'title' => __('Industry'),
                    'edit' => array(
                        'element'  => 'Custom\User\Form\Element\Industry',
                        'required' => true,
                    ),
                ),
                'sector'    => array(
                    'title' => __('Sector'),
                    'edit'  => 'hidden',
                ),
                'position'    => array(
                    'title' => __('Job Position'),
                    'edit'  => array(
                        'element'    => 'select',
                        'attributes' => array(
                            'options' => array(
                                ''                  => __('Select'),
                                __('R&D')           => __('R&D'),
                                __('Management')    => __('Management'),
                                __('Measurement')   => __('Measurement'),
                                __('QA')            => __('QA'),
                                __('Market')        => __('Market'),
                                __('Student')       => __('Student'),
                            ),
                        ),
                    ),
                ),
                'title'    => array(
                    'title' => __('Job title'),
                    'edit' => array(
                        'required' => true,
                    ),
                ),
                'description'   => array(
                    'title' => __('Description'),
                    'edit'  => array(
                        'element' => 'textarea',
                        'attributes' => array(
                            'rows'    => 5,
                            'class'   => 'input-block-level',
                        ),
                    ),
                ),
                'start'    => array(
                    'title' => __('Start time'),
                    'edit'  => 'Custom\User\Form\Element\StartTime',
                ),
                'end'    => array(
                    'title' => __('End time'),
                    'edit'  => 'Custom\User\Form\Element\EndTime',
                ),
            ),
        ),

            /*
        // Compound: Profession interests
        'interest'      => array(
            'name'  => 'interest',
            'title' => __('Interests'),

            // Custom handler
            'handler'   => 'Custom\User\Field\Interest',

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
            'handler'   => 'Custom\User\Field\Subscription',

            // Fields
            'field' => array(
                'item' => array(
                    'title' => __('Item'),
                ),
            ),
        ),
        */
    ),

    // Timeline logs from modules
    'timeline'  => array(
    ),

    // Activity logs
    'activity'  => array(
        // Community bbs
        'community_bbs'    => array(
            'title' => __('Community bbs'),
            'callback'  => 'Custom\User\Activity\CommunityBbs',
            'template'  => 'activity-community-bbs'
        ),

        // CNDZZ
        'cndzz'    => array(
            'title' => __('CNDZZ'),
            'callback'  => 'Custom\User\Activity\Cndzz',
            'template'  => 'activity-cndzz'
        ),

        // Blog
        'Blog'    => array(
            'title' => __('Blog'),
            'callback'  => 'Custom\User\Activity\Blog',
            'template'  => 'activity-blog'
        ),

        // Community
        'community'    => array(
            'title' => __('Community'),
            'callback'  => 'Custom\User\Activity\Community',
            'template'  => 'activity-community'
        ),
    ),

    // Quicklinks
    'quicklink' => array(
        'eefocus'    => array(
            'title' =>  __('EEFOCUS homepage'),
            'link'  => 'http://www.eefocus.com/',
            'icon'  => 'icon-eefocus',
        ),
    ),

);
