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
 * Class for initializing form of account
 *
 * @author Liu Chuang <liuchuang@eefocus.com>
 */
class AccountForm extends BaseForm
{
    public function init()
    {
        $this->add(array(
            'name'       => 'identity',
            'options'    => array(
                'label' => __('Username'),
            ),
            'type' => 'text',
            'attributes' => array(
                'disabled' => 'disabled'
            ),
        ));

        $this->add(array(
            'name'       => 'email',
            'options'    => array(
                'label' => __('Email'),
            ),
            'attributes' => array(
                'type' => 'text',
            ),
        ));

        $this->add(array(
            'name'       => 'name',
            'options'    => array(
                'label' => __('Display name'),
            ),
            'attributes' => array(
                'type' => 'text',
            ),
        ));

        $this->add(array(
            'name'       => 'uid',
            'attributes' => array(
                'type' => 'hidden',
            ),
        ));
        
        $this->add(array(
            'name'       => 'id',
            'attributes' => array(
                'type' => 'hidden',
            ),
        ));

        $this->add(array(
            'name'       => 'submit',
            'attributes' => array(
                'value' => __('Submit'),
            ),
            'type'       => 'submit',
        ));
    }
}