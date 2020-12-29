<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Form;

use Pi;
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
        $this->add([
            'name'       => 'identity',
            'options'    => [
                'label' => __('Username'),
            ],
            'type'       => 'text',
            'attributes' => [
                'disabled' => 'disabled',
            ],
        ]);

        $this->add([
            'name'       => 'email',
            'options'    => [
                'label' => __('Email'),
            ],
            'attributes' => [
                'type' => 'text',
            ],
        ]);

        $this->add([
            'name'       => 'name',
            'options'    => [
                'label' => __('Display name'),
            ],
            'attributes' => [
                'type' => 'text',
            ],
        ]);

        if (Pi::service('module')->isActive('subscription')) {
            $people = Pi::api('people', 'subscription')->getCurrentPeople();
            $description = null;
            if ($people  != null) {
                $description = sprintf(__('(updated on %s)'), _date($people['time_update']));
            }
            $this->add([
                'name'    => 'newsletter',
                'type'    => 'checkbox',
                'options' => [
                    'label' => __('Newsletter subscription'),

                ],
                'attributes' => [
                    'description' => $description,
                    'value' => (bool)$people
                ]
                
            ]);
        }

        $this->add([
            'name'       => 'uid',
            'attributes' => [
                'type' => 'hidden',
            ],
        ]);

        $this->add([
            'name'       => 'id',
            'attributes' => [
                'type' => 'hidden',
            ],
        ]);

        $this->add([
            'name'       => 'submit',
            'attributes' => [
                'value' => __('Submit'),
            ],
            'type'       => 'submit',
        ]);
    }
}
