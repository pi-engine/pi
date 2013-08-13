<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

/**
 * User compound profile specs
 *
 * @see Pi\Application\Installer\Resource\User
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */

return array(
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
);
