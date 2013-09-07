<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Form;

use Pi;
use Pi\Form\Form as BaseForm;

/**
 * Search form
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class SearchForm extends BaseForm
{
    public function init()
    {
        // Add state select
        $this->add(array(
            'name'          => 'state',
            'type'          => 'select',
            'options'       => array(
                'label' => __('State'),
                'value_options' => array(
                    'none' => __('None'),
                    'activated' => __('Activated'),
                    'pending'   => __('Pending'),
                ),
            )
        ));

        // Add able select
        $this->add(array(
            'name'          => 'enable',
            'type'          => 'select',
            'options'       => array(
                'label' => __('Enable'),
                'value_options' => array(
                    'none'    => __('None'),
                    'enable'  => __('Enable'),
                    'disable' => __('Disable'),
                ),
            )
        ));

        // Add front role
        $this->add(array(
            'name'          => 'front-role',
            'type'          => 'role',
            'options'       => array(
                'label'     => __('Front role'),
            ),
        ));

        // Add admin role
        $this->add(array(
            'name'          => 'admin-role',
            'type'          => 'role',
            'options'       => array(
                'label'     => __('Admin role'),
                'section'   => 'admin',
            ),
        ));

        // Add username
        $this->add(array(
            'name'          => 'identity',
            'type'          => 'text',
            'options'       => array(
                'label' => __('Username'),
            )
        ));

        // Add time created from
        $this->add(array(
            'type'          => 'date_select',
            'name'          => 'time-created-from',
            'options'       => array(
                'label' => __('Register date from'),
            ),
        ));

        // Add time created to
        $this->add(array(
            'type'          => 'date_select',
            'name'          => 'time-created-end',
            'options'       => array(
                'label' => __('Register date end'),
            ),
        ));

        // Add time login from
        $this->add(array(
            'type'          => 'date_select',
            'name'          => 'time-login-from',
            'options'       => array(
                'label' => __('Last login date from'),
            ),
        ));

        // Add time login from
        $this->add(array(
            'type'          => 'date_select',
            'name'          => 'time-login-to',
            'options'       => array(
                'label' => __('Last login date to'),
            ),
        ));

        // Add avatar select
        $this->add(array(
            'name'          => 'avatar',
            'type'          => 'select',
            'options'       => array(
                'label' => __('Avatar'),
                'value_options' => array(
                    'none'    => __('None'),
                    'with'    => __('With'),
                    'without' => __('Without'),
                ),
            )
        ));

        $this->add(array(
            'name'          => 'search',
            'type'          => 'submit',
            'attributes'    => array(
                'value' => __('Search'),
            ),
        ));
    }
}
