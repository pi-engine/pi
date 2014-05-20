<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Form;

//use Pi;
use Pi\Form\Form as BaseForm;

/**
 * Class for initializing form of search form
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class SearchForm extends BaseForm
{
    public function init()
    {
        // Add state select
        $this->add(array(
            'name'          => 'active',
            'type'          => 'select',
            'options'       => array(
                'label' => __('Active state'),
                'value_options' => array(
                    'any'         => __('Any'),
                    'active'   => __('Active'),
                    'inactive' => __('Inactive'),
                ),
            )
        ));

        // Add able select
        $this->add(array(
            'name'          => 'enable',
            'type'          => 'select',
            'options'       => array(
                'label' => __('Enable state'),
                'value_options' => array(
                    'any'        => __('Any'),
                    'enable'  => __('Enable'),
                    'disable' => __('Disable'),
                ),
            )
        ));

        // Add able select
        $this->add(array(
            'name'          => 'activated',
            'type'          => 'select',
            'options'       => array(
                'label' => __('Activated state'),
                'value_options' => array(
                    'any'          => __('Any'),
                    'pending'   => __('Pending'),
                    'activated' => __('Activated'),
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

        // Add name
        $this->add(array(
            'name'          => 'name',
            'type'          => 'text',
            'options'       => array(
                'label' => __('Display name'),
            )
        ));

        // Add email
        $this->add(array(
            'name'          => 'email',
            'type'          => 'text',
            'options'       => array(
                'label' => __('Email'),
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

        // Add register ip from
        $this->add(array(
            'name'          => 'ip-register',
            'type'          => 'text',
            'options'       => array(
                'label' => __('Register ip from'),
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
