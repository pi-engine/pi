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
use Pi\Application\Db\User\RowGateway\Account;

/**
 * Member form
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class MemberForm extends BaseForm
{
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

        // Add activate checkbox
        $this->add(array(
            'name'          => 'activate',
            'type'          => 'checkbox',
            'options'       => array(
                'label' => __('Activate'),
            ),
            'attributes'    => array(
                'value' => 1,
            ),
        ));

        // Add enable checkbox
        $this->add(array(
            'name'          => 'enable',
            'type'          => 'checkbox',
            'options'       => array(
                'label' => __('Enable'),
            ),
            'attributes'    => array(
                'value' => 1,
            ),
        ));

        $this->add(array(
            'name'  => 'security',
            'type'  => 'csrf',
        ));

        $this->add(array(
            'name'          => 'submit',
            'type'          => 'submit',
            'attributes'    => array(
                'value' => __('Submit'),
            ),
        ));
    }
}
