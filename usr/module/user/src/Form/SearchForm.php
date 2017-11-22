<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
        $this->add([
            'name'    => 'active',
            'type'    => 'select',
            'options' => [
                'label'         => __('Active state'),
                'value_options' => [
                    'any'      => __('Any'),
                    'active'   => __('Active'),
                    'inactive' => __('Inactive'),
                ],
            ],
        ]);

        // Add able select
        $this->add([
            'name'    => 'enable',
            'type'    => 'select',
            'options' => [
                'label'         => __('Enable state'),
                'value_options' => [
                    'any'     => __('Any'),
                    'enable'  => __('Enable'),
                    'disable' => __('Disable'),
                ],
            ],
        ]);

        // Add able select
        $this->add([
            'name'    => 'activated',
            'type'    => 'select',
            'options' => [
                'label'         => __('Activated state'),
                'value_options' => [
                    'any'       => __('Any'),
                    'pending'   => __('Pending'),
                    'activated' => __('Activated'),
                ],
            ],
        ]);

        // Add front role
        $this->add([
            'name'    => 'front-role',
            'type'    => 'role',
            'options' => [
                'label' => __('Front role'),
            ],
        ]);

        // Add admin role
        $this->add([
            'name'    => 'admin-role',
            'type'    => 'role',
            'options' => [
                'label'   => __('Admin role'),
                'section' => 'admin',
            ],
        ]);

        // Add username
        $this->add([
            'name'    => 'identity',
            'type'    => 'text',
            'options' => [
                'label' => __('Username'),
            ],
        ]);

        // Add name
        $this->add([
            'name'    => 'name',
            'type'    => 'text',
            'options' => [
                'label' => __('Display name'),
            ],
        ]);

        // Add email
        $this->add([
            'name'    => 'email',
            'type'    => 'text',
            'options' => [
                'label' => __('Email'),
            ],
        ]);

        // Add time created from
        $this->add([
            'type'    => 'date_select',
            'name'    => 'time-created-from',
            'options' => [
                'label' => __('Register date from'),
            ],
        ]);

        // Add time created to
        $this->add([
            'type'    => 'date_select',
            'name'    => 'time-created-end',
            'options' => [
                'label' => __('Register date end'),
            ],
        ]);

        // Add register ip from
        $this->add([
            'name'    => 'ip-register',
            'type'    => 'text',
            'options' => [
                'label' => __('Register ip from'),
            ],
        ]);

        $this->add([
            'name'       => 'search',
            'type'       => 'submit',
            'attributes' => [
                'value' => __('Search'),
            ],
        ]);
    }
}
