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

        if(Pi::service('module')->isActive('subscription')){
            $this->add(array(
                'name'       => 'newsletter',
                'type'      => 'checkbox',
                'options'    => array(
                    'label' => __('Newsletter subscription'),
                ),
            ));

            $people = $this->_getCurrentPeople();

            $this->get('newsletter')->setValue((bool) $people);
        }

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

    protected function _getCurrentPeople(){
        $peopleModel = $this->getPeopleModel();
        $select = $peopleModel->select();
        $select->where(
            array(
                'uid' => Pi::user()->getId(),
                'campaign' => 0,
            )
        );

        $people = $peopleModel->selectWith($select)->current();

        return $people;
    }

    protected function getPeopleModel(){
        return Pi::model('people', 'subscription');
    }

    public function isValid()
    {
        $isValid = parent::isValid();

        if($isValid && Pi::service('module')->isActive('subscription')){
            $newsletterValue = $this->get('newsletter')->getValue();
            $people = $this->_getCurrentPeople();

            if($newsletterValue == 1 && !$people){
                $peopleModel = $this->getPeopleModel();
                $people = $peopleModel->createRow();

                $values = array();
                $values['campaign'] = 0;
                $values['uid'] = Pi::user()->getId();
                $values['status'] = 1;
                $values['time_join'] = time();
                $values['newsletter'] = 1;
                $values['email'] = null;
                $values['mobile'] = null;

                $people->assign($values);
                $people->save();

                $log = array(
                    'uid' => Pi::user()->getId(),
                    'action' => 'subscribe_newsletter_account',
                );

                Pi::api('log', 'user')->add(null, null, $log);

            } elseif($newsletterValue == 0 && $people){
                $people->delete();

                $log = array(
                    'uid' => Pi::user()->getId(),
                    'action' => 'unsubscribe_newsletter_account',
                );

                Pi::api('log', 'user')->add(null, null, $log);
            }
        }

        return $isValid;
    }
}