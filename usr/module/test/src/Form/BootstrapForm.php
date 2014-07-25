<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 14-7-17
 * Time: 下午6:15
 */

namespace Module\Test\Form;

use Pi;
use Pi\Form\Form as BaseForm;
use Zend\Form;
use Zend\Form\Element;

class BootstrapForm extends BaseForm
{
    protected $config = array();

    public function __construct($name, array $config = array())
    {
        if (!$config) {
            $config = Pi::config('', 'test', '');
        }
        $this->config  = $config;
        parent::__construct($name);
    }

    public function init()
    {
        // Adding a text form

        if (!empty($this->config['login_disable'])) {
            $this->add(array(
                'name'          => 'username',
                'attributes'    => array(
                    'type'  => 'text',
                ),
                'options' => array(
                    'label' => __('Username'),
                ),
            ));
        }

        //adding a text form
        if (!empty($this->config['phone_disable'])) {
            $this->add(array(
                'name'          => 'phone',
                'attributes'    => array(
                    'type'  => 'text',
                ),
                'options' => array(
                    'label' => __('Phone'),
                ),
            ));
        }

        //adding a text form
        if (!empty($this->config['email_disable'])) {
            $this->add(array(
                'name'          => 'email',
                'attributes'    => array(
                    'type'  => 'text',
                ),
                'options' => array(
                    'label' => __('Email'),
                ),
            ));
        }

        // Adding a textarea form
        if (!empty($this->config['message_disable'])) {
            $this->add(array(
                'name'          => 'content',
                'attributes'    => array(
                    'type'  => 'textarea',
                    'cols'  => '50',
                    'rows'  => '10',
                ),
                'options'       => array(
                    'label' => __('Your details'),
                ),
            ));
        }
/*
        if (!empty($config['username'])) {
            $this->add(array(
                'name'          => 'username',
                'type'          => 'checkbox',
                'options'       => array(
                    'label' => __('Username'),
                ),
                'attributes'    => array(
                    'value'         => '1',
                    'description'   => __('Username')
                )
            ));
        }
*/
        $this->add(array(
            'name'          => 'submit',
            'type'          => 'submit',
            'attributes'    => array(
                'value' => __('提交'),
                'class' => 'btn btn-primary'
            )
        ));
    }
}