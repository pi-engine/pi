<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link         http://code.pialog.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://pialog.org
 * @license      http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Form;

use Pi;
use Pi\Form\Form as BaseForm;

/**
 * Class for initializing form of edit user info
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class EditUserForm extends BaseForm
{
    /**
     * Initalizing form
     */
    public function init()
    {
        // Add user name
        $this->add(array(
            'type'          => 'text',
            'name'          => 'identity',
            'options'       => array(
                'label' => __('Username'),
            )
        ));

        // Add display name
        $this->add(array(
            'type'          => 'text',
            'name'          => 'name',
            'options'       => array(
                'label' => __('Display name'),
            )
        ));

        // Add email
        $this->add(array(
            'type'          => 'email',
            'name'          => 'email',
            'options'       => array(
                'label' => __('Email'),
            ),
        ));

        // Add password
        $this->add(array(
            'type'          => 'password',
            'name'          => 'credential',
            'options'       => array(
                'label' => __('New password'),
            ),
        ));

        // Confirm password
        $this->add(array(
            'type'          => 'password',
            'name'          => 'credential-confirm',
            'options'       => array(
                'label' => __('Confirm password'),
            ),
        ));
    }
}
