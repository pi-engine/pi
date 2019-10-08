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

//use Pi\Application\Db\User\RowGateway\Account;

/**
 * Class for initializing form of member
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class MemberForm extends BaseForm
{
    public function init()
    {
        // Add user name
        $this->add([
            'type'    => 'text',
            'name'    => 'identity',
            'options' => [
                'label' => __('Username'),
            ],
        ]);

        // Add display name
        $this->add([
            'type'    => 'text',
            'name'    => 'name',
            'options' => [
                'label' => __('Display name'),
            ],
        ]);

        // Add email
        $this->add([
            'type'    => 'email',
            'name'    => 'email',
            'options' => [
                'label' => __('Email'),
            ],
        ]);

        // Add password
        $this->add([
            'type'    => 'password',
            'name'    => 'credential',
            'options' => [
                'label' => __('New password'),
            ],
        ]);

        // Confirm password
        $this->add([
            'type'    => 'password',
            'name'    => 'credential-confirm',
            'options' => [
                'label' => __('Confirm password'),
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

        // Add activate checkbox
        $this->add([
            'name'       => 'activate',
            'type'       => 'checkbox',
            'options'    => [
                'label' => __('Activate'),
            ],
            'attributes' => [
                'value' => 1,
            ],
        ]);

        // Add enable checkbox
        $this->add([
            'name'       => 'enable',
            'type'       => 'checkbox',
            'options'    => [
                'label' => __('Enable'),
            ],
            'attributes' => [
                'value' => 1,
            ],
        ]);

        $this->add([
            'name' => 'security',
            'type' => 'csrf',
        ]);

        $this->add([
            'name'       => 'submit',
            'type'       => 'submit',
            'attributes' => [
                'value' => __('Submit'),
            ],
        ]);
    }
}
