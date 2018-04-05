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
            $this->add([
                'name'    => 'newsletter',
                'type'    => 'checkbox',
                'options' => [
                    'label' => __('Newsletter subscription'),
                    
                ],
                'attributes' => [
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

    public function isValid()
    {
        $isValid = parent::isValid();

        if ($isValid && Pi::service('module')->isActive('subscription')) {
            $newsletterValue = $this->get('newsletter')->getValue();
            $people          = Pi::api('people', 'subscription')->getCurrentPeople();
            if ($newsletterValue == 1 && !$people) {
                
                $values               = [];
                $values['campaign']   = 0;
                $values['uid']        = Pi::user()->getId();
                $values['status']     = 1;
                $values['time_join']  = time();
                $values['newsletter'] = 1;
                $values['email']      = null;
                $values['mobile']     = null;
                
                Pi::api('people', 'subscription')->createPeople($values);
               
                $log = [
                    'uid'    => Pi::user()->getId(),
                    'action' => 'subscribe_newsletter_account',
                ];

                Pi::api('log', 'user')->add(null, null, $log);

            } elseif ($newsletterValue == 0 && $people) {
                $people->delete();

                $log = [
                    'uid'    => Pi::user()->getId(),
                    'action' => 'unsubscribe_newsletter_account',
                ];

                Pi::api('log', 'user')->add(null, null, $log);
            }
        }

        return $isValid;
    }
}